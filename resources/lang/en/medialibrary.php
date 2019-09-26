<?php

return [
    'constraint' => [
        'dimensions' => [
            'both'   => 'Min. width : :width px / Min. height : :height px.',
            'width'  => 'Min. width : :width px.',
            'height' => 'Min. height : :height px.',
        ],
        'types'      => '{1}Accepted type : :types.|[2,*]Accepted types : :types.',
    ],
];
