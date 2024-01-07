<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    public function up()
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Relation avec l'utilisateur (Artist)
            $table->string('title');
            $table->string('artist_name'); // Ajout du nom de l'artiste
            $table->string('file_path'); // Chemin vers le fichier audio du Son
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('songs');
    }
}

