<?php

$poi_types = array(

    "basic_hut" => array("Abri sommaire", "Abri", "#ef2929", "basic_hut",
        "Un abri sommaire est un bâtiment qui ne permet pas l'hébergement, comme un kiosque.",
        array(
            't_owner'       => "👤 Informations sur le⋅la propriétaire et moyens de contacts",
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description sur l'abri et remarques",
            'b_usable'      => "🚫 Abri condamné, détruit ou fermé ?",
            'b_water'       => "💧 Eau à proximité ?",
            'b_wood'        => "🌲 Bois à proximité ?"
        )
    ),

    "wilderness_hut" => array("Cabane non gardée", "Cabane", "#ef2929", "wilderness_hut",
        "Une cabane non gardée est un bâtiment qui permet l'hébergement, même sommaire, sans gardien.",
        array(
            't_owner'       => "👤 Informations sur le⋅la propriétaire et moyens de contacts",
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description sur la cabane et remarques",
            'b_key'         => "🔑 Nécessite une clé ?",
            'b_usable'      => "🚫 Cabane condamnée, détruite ou fermée ?",
            'n_bed'         => "🛏️ Nombre de places prévues pour dormir :",
            'n_mattress'    => "🛌 Nombre de matelas disponibles :",
            'b_cover'       => "🧣 Couvertures ?",
            'b_water'       => "💧 Eau à proximité ?",
            'b_wood'        => "🌲 Bois à proximité ?",
            'b_fireplace'   => "🔥 Cheminée ou poêle à bois ?",
            'b_toilet'      => "🚽 Latrines ou toilettes ?"
        )
    ),

    "alpine_hut" => array("Refuge gardé", "Refuge", "#ef2929", "alpine_hut",
        "Un refuge gardé est un bâtiment qui permet l'hébergement toute l'année, gardé tout ou partie de l'année.",
        array(
            't_owner'       => "👤 Informations sur le⋅la propriétaire, le⋅la gardien⋅ne et moyens de contacts",
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description sur le refuge et remarques",
            'b_usable'      => "🚫 Refuge condamné, détruit ou fermé ?",
            'n_bed'         => "Places en période gardée ☀️ :",
            'n_bed_winter'  => "Places en période non gardée ❄️ :",
            'n_mattress'    => "🛌 Matelas en période non gardée :",
            'b_cover'       => "🧣 Couvertures disponibles ?",
            'b_water'       => "💧 Possibilité de se ravitailler en eau ?",
            'b_wood'        => "🌲 Bois à proximité ?",
            'b_fireplace'   => "🔥 Cheminée ou poêle à bois ?",
            'b_toilet'      => "🚽 Latrines ou toilettes ?",
            'l_water'       => "URL du site web :"
        )
    ),

    "halt" => array("Gîte d'étape", "Gîte", "#4e9a06", "halt",
        "Un gîte d'étape est un bâtiment qui permet l'hébergement uniquement sur ses périodes d'ouvertures.",
        array(
            't_owner'       => "👤 Informations sur le⋅la propriétaire, le⋅la gardien⋅ne et moyens de contacts",
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description sur le gîte et remarques",
            'b_usable'      => "🚫 Gîte condamné, détruit ou fermé ?",
            'n_bed'         => "🛏️ Nombre de places prévues pour dormir :",
            'b_water'       => "💧 Possibilité de se ravitailler en eau ?",
            'l_water'       => "URL du site web :"
        )
    ),

    "bivouac" => array("Zone de bivouac", "Bivouac", "#ef2929", "bivouac",
        "Une zone de bivouac est un espace aménagé permettant de planter la tente.",
        array(
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description sur la zone de bivouac et remarques",
            'n_bed'         => "⛺ Nombre d'emplacements :",
            'b_water'       => "💧 Eau à proximité ?",
            'b_wood'        => "🌲 Bois à proximité ?",
            'b_fireplace'   => "🔥 Emplacement pour faire un feu ?"
        )
    ),

    "campsite" => array("Camping", "Camping", "#4e9a06", "campsite",
        "Un camping est un espace aménagé permettant de planter la tente plusieurs jours, avec gardien.",
        array(
            't_owner'       => "👤 Informations sur le⋅la propriétaire, le⋅la gardien⋅ne et moyens de contacts",
            't_access'      => "🧭 Description de l'accès, des transports en commun, et d'éventuels passages délicats",
            't_description' => "📝 Description du camping et remarques",
            'n_bed'         => "⛺ Nombre d'emplacements :",
            'b_water'       => "💧 Possibilité de se ravitailler en eau ?",
            'l_water'       => "URL du site web :"
        )
    )

);

?>
