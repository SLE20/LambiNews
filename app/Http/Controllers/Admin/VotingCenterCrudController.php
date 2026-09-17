<?php

namespace App\Http\Controllers\Admin;

use App\Models\VotingCenter;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingCenterCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;

    public function setup(): void
    {
        abort_unless(backpack_user()?->isEditor(), 403);

        CRUD::setModel(VotingCenter::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/voting-center');
        CRUD::setEntityNameStrings('centre de vote', 'centres d’inscription et de vote');
        CRUD::setSubheading(VotingCenter::count().' centre(s) · <a href="'
            .backpack_url('voting-center-import').'">Importer la liste du CEP (CSV)</a>');
    }

    protected function setupListOperation(): void
    {
        CRUD::addColumn(['name' => 'department', 'label' => 'Département']);
        CRUD::addColumn(['name' => 'commune', 'label' => 'Commune']);
        CRUD::addColumn(['name' => 'section', 'label' => 'Section']);
        CRUD::addColumn(['name' => 'name', 'label' => 'Centre', 'limit' => 60]);
        CRUD::addColumn(['name' => 'address', 'label' => 'Adresse', 'limit' => 60]);
        CRUD::orderBy('department')->orderBy('commune');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation([
            'department' => 'required|in:'.implode(',', array_keys(VotingCenter::DEPARTMENTS)),
            'commune'    => 'required|string|max:80',
            'name'       => 'required|string|max:255',
        ]);

        CRUD::addField([
            'name'    => 'department',
            'label'   => 'Département',
            'type'    => 'select_from_array',
            'options' => array_combine(array_keys(VotingCenter::DEPARTMENTS), array_column(VotingCenter::DEPARTMENTS, 0)),
        ]);
        CRUD::addField(['name' => 'commune', 'label' => 'Commune', 'type' => 'text']);
        CRUD::addField(['name' => 'section', 'label' => 'Section communale', 'type' => 'text']);
        CRUD::addField(['name' => 'name', 'label' => 'Nom du centre', 'type' => 'text']);
        CRUD::addField(['name' => 'address', 'label' => 'Adresse', 'type' => 'text']);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    public function importForm(): View
    {
        return view('admin.voting-center-import', [
            'departments' => VotingCenter::DEPARTMENTS,
            'count'       => VotingCenter::count(),
        ]);
    }

    /**
     * Remplace toute la liste par le fichier fourni.
     *
     * Colonnes attendues (avec en-tête) : departement, commune, section,
     * nom, adresse. Séparateur virgule ou point-virgule, UTF-8. Le fichier
     * est entièrement validé avant d'effacer quoi que ce soit.
     */
    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => 'required|file|max:10240|mimes:csv,txt']);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $first  = (string) fgets($handle);
        $sep    = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $header = array_map(
            fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))),
            str_getcsv($first, $sep)
        );

        $aliases = [
            'department' => ['departement', 'département', 'department'],
            'commune'    => ['commune'],
            'section'    => ['section', 'section communale'],
            'name'       => ['nom', 'centre', 'name'],
            'address'    => ['adresse', 'address'],
        ];
        $index = [];
        foreach ($aliases as $field => $names) {
            $found = array_values(array_intersect($names, $header));
            $index[$field] = $found ? array_search($found[0], $header, true) : null;
        }

        if ($index['department'] === null || $index['commune'] === null || $index['name'] === null) {
            \Alert::error('En-tête invalide : il faut au moins les colonnes departement, commune et nom.')->flash();

            return back();
        }

        $rows = [];
        $errors = [];
        $line = 1;
        $now = now();

        while (($data = fgetcsv($handle, 0, $sep)) !== false) {
            $line++;
            if (count(array_filter($data, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            $get = fn ($f) => $index[$f] === null ? null : (trim((string) ($data[$index[$f]] ?? '')) ?: null);
            $dept = strtoupper((string) $get('department'));
            $dept = str_replace(['’', 'É'], ["'", 'E'], $dept);

            if (! isset(VotingCenter::DEPARTMENTS[$dept])) {
                $errors[] = "ligne {$line} : département inconnu « {$get('department')} »";
                continue;
            }
            if (! $get('commune') || ! $get('name')) {
                $errors[] = "ligne {$line} : commune ou nom manquant";
                continue;
            }

            $rows[] = [
                'department' => $dept,
                'commune'    => mb_substr($get('commune'), 0, 80),
                'section'    => $get('section') ? mb_substr($get('section'), 0, 120) : null,
                'name'       => mb_substr($get('name'), 0, 255),
                'address'    => $get('address') ? mb_substr($get('address'), 0, 300) : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        fclose($handle);

        if ($errors) {
            \Alert::error('Import annulé, rien n’a été modifié. '.count($errors).' erreur(s) : '
                .implode(' ; ', array_slice($errors, 0, 5)))->flash();

            return back();
        }

        if (! $rows) {
            \Alert::error('Le fichier ne contient aucun centre.')->flash();

            return back();
        }

        DB::transaction(function () use ($rows): void {
            VotingCenter::query()->delete();
            foreach (array_chunk($rows, 500) as $chunk) {
                VotingCenter::insert($chunk);
            }
        });

        \Alert::success(count($rows).' centres importés.')->flash();

        return redirect(backpack_url('voting-center'));
    }
}
