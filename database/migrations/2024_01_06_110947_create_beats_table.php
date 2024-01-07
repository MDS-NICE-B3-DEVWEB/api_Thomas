<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeatsTable extends Migration
{
    public function up()
    {
        Schema::create('beats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Relation avec l'utilisateur (Beatmaker)
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path'); // Chemin vers le fichier audio du Beat
            $table->string('beatmaker_name'); // Ajout du nom du Beatmaker
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('beats');
    }
}
