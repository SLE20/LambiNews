@extends(backpack_view('blank'))

@section('content')
<div class="container-fluid" style="max-width:860px;padding-top:1rem">
    <h1 class="h3 mb-1">Importer les centres d’inscription et de vote</h1>
    <p class="text-muted">Liste actuelle : {{ $count }} centre(s). L’import <strong>remplace</strong> toute la liste,
        et seulement si le fichier entier est valide.</p>

    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ backpack_url('voting-center-import') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="civ-file">Fichier CSV (UTF-8)</label>
                    <input class="form-control" type="file" name="file" id="civ-file" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Remplacer toute la liste des centres ?')">Importer</button>
                <a href="{{ backpack_url('voting-center') }}" class="btn btn-link">Annuler</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="h6">Format attendu</h2>
            <p class="mb-2">Une ligne d’en-tête, séparateur virgule ou point-virgule :</p>
            <pre class="bg-light p-2 rounded mb-3">departement;commune;section;nom;adresse;page
ARTIBONITE;GONAÏVES;VILLE;LYCÉE FABRE GEFFRARD;Rue Lamartinière;3</pre>
            <p class="mb-1">Départements acceptés :</p>
            <p class="small text-muted mb-0">{{ implode(' · ', array_keys($departments)) }}</p>
        </div>
    </div>
</div>
@endsection
