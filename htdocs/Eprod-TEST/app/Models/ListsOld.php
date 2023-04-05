<?php

    namespace App\Models;

    Class ListsOld {
        public static function all() {
            return [
                [
                    'id' => 1,
                    'title' => 'First Item',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                ],
                [
                    'id' => 2,
                    'title' => 'Second Item',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                ],
                [
                    'id' => 3,
                    'title' => 'Third Item',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                ],
                [
                    'id' => 4,
                    'title' => 'Fourth Item',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                ],
                [
                    'id' => 5,
                    'title' => 'Fif Item',
                    'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'
                ]
            ];
        }
        public static function find($id) {
            $items = self::all();

            foreach($items as $listItem) {
                if($listItem['id'] == $id) {
                    return $listItem;
                }
            }
        }
    
    }

?>