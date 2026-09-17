<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

/** Centre d'inscription et de vote (CIV), d'après la liste du CEP. */
class VotingCenter extends Model
{
    use CrudTrait;

    /**
     * Chefs-lieux des départements : le fichier du CEP ne donne pas de
     * coordonnées, la carte regroupe donc les centres par département.
     */
    public const DEPARTMENTS = [
        'ARTIBONITE' => ['Artibonite', 'Gonaïves', 19.4515, -72.6890],
        'CENTRE'     => ['Centre', 'Hinche', 19.1450, -72.0040],
        "GRAND'ANSE" => ['Grand’Anse', 'Jérémie', 18.6500, -74.1167],
        'NIPPES'     => ['Nippes', 'Miragoâne', 18.4460, -73.0896],
        'NORD'       => ['Nord', 'Cap-Haïtien', 19.7580, -72.2040],
        'NORD-EST'   => ['Nord-Est', 'Fort-Liberté', 19.6630, -71.8380],
        'NORD-OUEST' => ['Nord-Ouest', 'Port-de-Paix', 19.9390, -72.8300],
        'OUEST'      => ['Ouest', 'Port-au-Prince', 18.5392, -72.3350],
        'SUD'        => ['Sud', 'Les Cayes', 18.1930, -73.7460],
        'SUD-EST'    => ['Sud-Est', 'Jacmel', 18.2340, -72.5350],
    ];

    protected $fillable = ['department', 'commune', 'section', 'name', 'address'];

    public function departmentLabel(): string
    {
        return self::DEPARTMENTS[$this->department][0] ?? $this->department;
    }
}
