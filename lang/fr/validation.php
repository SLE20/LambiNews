<?php

/*
 * Messages de validation en français.
 *
 * L’application tourne en locale « fr » ; sans ce fichier Laravel affiche
 * la clé brute (« validation.email ») à l’utilisateur.
 */

return [
    'accepted' => "Le champ :attribute doit être accepté.",
    'active_url' => "Le champ :attribute n’est pas une URL valide.",
    'after' => "Le champ :attribute doit être une date postérieure au :date.",
    'alpha' => "Le champ :attribute ne peut contenir que des lettres.",
    'alpha_dash' => "Le champ :attribute ne peut contenir que des lettres, chiffres, tirets et underscores.",
    'alpha_num' => "Le champ :attribute ne peut contenir que des lettres et des chiffres.",
    'array' => "Le champ :attribute doit être un tableau.",
    'before' => "Le champ :attribute doit être une date antérieure au :date.",
    'boolean' => "Le champ :attribute doit être vrai ou faux.",
    'confirmed' => "La confirmation du champ :attribute ne correspond pas.",
    'date' => "Le champ :attribute n’est pas une date valide.",
    'date_equals' => "Le champ :attribute doit être une date égale à :date.",
    'different' => "Les champs :attribute et :other doivent être différents.",
    'digits' => "Le champ :attribute doit contenir :digits chiffres.",
    'email' => "Le champ :attribute doit être une adresse courriel valide.",
    'exists' => "La valeur du champ :attribute est invalide.",
    'file' => "Le champ :attribute doit être un fichier.",
    'filled' => "Le champ :attribute doit avoir une valeur.",
    'image' => "Le champ :attribute doit être une image.",
    'in' => "Le champ :attribute est invalide.",
    'integer' => "Le champ :attribute doit être un entier.",
    'ip' => "Le champ :attribute doit être une adresse IP valide.",
    'json' => "Le champ :attribute doit être un document JSON valide.",
    'mimes' => "Le champ :attribute doit être un fichier de type : :values.",
    'not_in' => "Le champ :attribute sélectionné est invalide.",
    'numeric' => "Le champ :attribute doit être un nombre.",
    'present' => "Le champ :attribute doit être présent.",
    'prohibited' => "Le champ :attribute est interdit.",
    'regex' => "Le format du champ :attribute est invalide.",
    'required' => "Le champ :attribute est obligatoire.",
    'required_if' => "Le champ :attribute est obligatoire quand :other vaut :value.",
    'required_with' => "Le champ :attribute est obligatoire quand :values est présent.",
    'same' => "Les champs :attribute et :other doivent être identiques.",
    'string' => "Le champ :attribute doit être une chaîne de caractères.",
    'timezone' => "Le champ :attribute doit être un fuseau horaire valide.",
    'unique' => "La valeur du champ :attribute est déjà utilisée.",
    'uploaded' => "Le fichier :attribute n’a pas pu être téléversé.",
    'url' => "Le format du champ :attribute est invalide.",
    'uuid' => "Le champ :attribute doit être un UUID valide.",

    'between' => [
        'array' => "Le champ :attribute doit contenir entre :min et :max éléments.",
        'file' => "Le fichier :attribute doit faire entre :min et :max kilo-octets.",
        'numeric' => "Le champ :attribute doit être compris entre :min et :max.",
        'string' => "Le champ :attribute doit contenir entre :min et :max caractères.",
    ],
    'max' => [
        'array' => "Le champ :attribute ne peut pas contenir plus de :max éléments.",
        'file' => "Le fichier :attribute ne peut pas dépasser :max kilo-octets.",
        'numeric' => "Le champ :attribute ne peut pas dépasser :max.",
        'string' => "Le champ :attribute ne peut pas dépasser :max caractères.",
    ],
    'min' => [
        'array' => "Le champ :attribute doit contenir au moins :min éléments.",
        'file' => "Le fichier :attribute doit faire au moins :min kilo-octets.",
        'numeric' => "Le champ :attribute doit être au minimum de :min.",
        'string' => "Le champ :attribute doit contenir au moins :min caractères.",
    ],
    'size' => [
        'array' => "Le champ :attribute doit contenir :size éléments.",
        'file' => "Le fichier :attribute doit faire :size kilo-octets.",
        'numeric' => "Le champ :attribute doit être égal à :size.",
        'string' => "Le champ :attribute doit contenir :size caractères.",
    ],

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'message-personnalise',
        ],
    ],

    'attributes' => [
        'amount' => 'montant',
        'author_id' => 'auteur',
        'body' => 'texte',
        'category_id' => 'rubrique',
        'content' => 'contenu',
        'description' => 'description',
        'donor_email' => 'courriel',
        'donor_name' => 'nom',
        'email' => 'courriel',
        'excerpt' => 'chapô',
        'featured_image' => 'image principale',
        'is_anonymous' => 'anonymat',
        'message' => 'message',
        'name' => 'nom',
        'password' => 'mot de passe',
        'phone' => 'téléphone',
        'published_at' => 'date de publication',
        'q' => 'recherche',
        'slug' => 'identifiant',
        'subject' => 'sujet',
        'title' => 'titre',
    ],
];
