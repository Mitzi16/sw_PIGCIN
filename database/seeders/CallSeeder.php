<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('calls')->insert([
            [
                'title' => 'Convocatoria 1',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 2',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 3',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 4',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 5',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 6',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 7',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
            [
                'title' => 'Convocatoria 8',
                'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam orci ante, dignissim non viverra vel, feugiat in tellus. Sed non mauris at velit tincidunt sagittis. Nullam a ullamcorper nunc.',
                'start_date' => '2024-07-27',
                'end_date' => '2024-07-27',
                'url' => 'https://www.youtube.com/@cenidet8939',
                'status' => true,
                'institution_id' => 1,
            ],
        ]);
    }
}
