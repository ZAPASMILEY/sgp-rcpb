<?php

return [
    'accepted'             => 'Le champ :attribute doit être accepté.',
    'array'                => 'Le champ :attribute doit être un tableau.',
    'boolean'              => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed'            => 'La confirmation du champ :attribute ne correspond pas.',
    'date'                 => 'Le champ :attribute n\'est pas une date valide.',
    'date_format'          => 'Le champ :attribute ne correspond pas au format :format.',
    'email'                => 'Le champ :attribute doit être une adresse email valide.',
    'exists'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'file'                 => 'Le champ :attribute doit être un fichier.',
    'gt'                   => [
        'numeric' => 'La valeur de :attribute doit être supérieure à :value.',
        'string'  => 'Le champ :attribute doit contenir plus de :value caractères.',
    ],
    'gte'                  => [
        'numeric' => 'La valeur de :attribute doit être supérieure ou égale à :value.',
    ],
    'image'                => 'Le champ :attribute doit être une image.',
    'in'                   => 'La valeur sélectionnée pour :attribute est invalide.',
    'integer'              => 'Le champ :attribute doit être un entier.',
    'lt'                   => [
        'numeric' => 'La valeur de :attribute doit être inférieure à :value.',
    ],
    'lte'                  => [
        'numeric' => 'La valeur de :attribute doit être inférieure ou égale à :value.',
    ],
    'max'                  => [
        'numeric' => 'La valeur de :attribute ne peut pas dépasser :max.',
        'string'  => 'Le champ :attribute ne peut pas contenir plus de :max caractères.',
        'array'   => 'Le champ :attribute ne peut pas avoir plus de :max éléments.',
        'file'    => 'Le fichier :attribute ne peut pas dépasser :max kilo-octets.',
    ],
    'min'                  => [
        'numeric' => 'La valeur de :attribute doit être au moins :min.',
        'string'  => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array'   => 'Le champ :attribute doit avoir au moins :min éléments.',
        'file'    => 'Le fichier :attribute doit faire au moins :min kilo-octets.',
    ],
    'not_in'               => 'La valeur sélectionnée pour :attribute est invalide.',
    'numeric'              => 'Le champ :attribute doit être un nombre.',
    'password'             => 'Le mot de passe est incorrect.',
    'present'              => 'Le champ :attribute doit être présent.',
    'regex'                => 'Le format du champ :attribute est invalide.',
    'required'             => 'Le champ :attribute est obligatoire.',
    'required_if'          => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_unless'      => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with'        => 'Le champ :attribute est obligatoire quand :values est présent.',
    'required_with_all'    => 'Le champ :attribute est obligatoire quand :values sont présents.',
    'required_without'     => 'Le champ :attribute est obligatoire quand :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire quand aucun de :values n\'est présent.',
    'same'                 => 'Les champs :attribute et :other doivent correspondre.',
    'size'                 => [
        'numeric' => 'La valeur de :attribute doit être :size.',
        'string'  => 'Le champ :attribute doit contenir :size caractères.',
        'array'   => 'Le tableau :attribute doit contenir :size éléments.',
        'file'    => 'Le fichier :attribute doit peser :size kilo-octets.',
    ],
    'string'               => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone'             => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique'               => 'La valeur du champ :attribute est déjà utilisée.',
    'url'                  => 'Le format du champ :attribute est invalide.',
    'uuid'                 => 'Le champ :attribute doit être un UUID valide.',
    'mimes'                => 'Le fichier :attribute doit être de type : :values.',
    'mimetypes'            => 'Le fichier :attribute doit être de type : :values.',
    'nullable'             => '',

    'custom' => [
        'identification.grade' => [
            'required' => 'Le grade de l\'évalué est obligatoire.',
        ],
        'identification.date_evaluation' => [
            'required' => 'La date d\'évaluation est obligatoire.',
        ],
        'subjective_criteres' => [
            'required' => 'Au moins un critère subjectif est requis.',
            'min'      => 'Au moins un critère subjectif est requis.',
        ],
        'objective_criteres' => [
            'required' => 'Au moins un critère objectif est requis.',
            'min'      => 'Au moins un critère objectif est requis.',
        ],
    ],

    'attributes' => [
        'identification.grade'           => 'grade',
        'identification.date_evaluation' => 'date d\'évaluation',
        'identification.formations'      => 'formations',
        'identification.experiences'     => 'expériences',
        'subjective_criteres'            => 'critères subjectifs',
        'objective_criteres'             => 'critères objectifs',
        'commentaire'                    => 'commentaire',
        'points_a_ameliorer'             => 'points à améliorer',
        'strategies_amelioration'        => 'stratégies d\'amélioration',
    ],
];
