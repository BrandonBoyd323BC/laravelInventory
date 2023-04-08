<?php 
    namespace App\Models;

    class Links {
        public static function getAllLinks() {
            return [
                [
                    'link' => 'https://laracasts.com/',
                    'description' => 'We are kinda like Netflix, but for developers! Push your programming skills to the next level, through expert screencasts on PHP, Laravel, Vue, and so much more.'               
                ],
                [
                    'link' => 'https://www.youtube.com/watch?v=MYyJ4PuL4pY',
                    'description' => 'Laravel From Scratch 2022 | 4+ Hour Course'
                
                ],
                [
                    'link' => 'https://laravel.com/docs/10.x',
                    'description' => 'Laravel is a web application framework with expressive, elegant syntax.'
                
                ]
            ];
        }
    }