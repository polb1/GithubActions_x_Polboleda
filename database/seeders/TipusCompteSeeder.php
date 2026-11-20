<?php

namespace Database\Seeders;

use App\Models\TipusCompte;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipusCompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    //Insertem les dades a la nostre base de dades a tipus de compte
    public function run(): void
    {
        TipusCompte::firstOrCreate([
            'nom' => 'Corrent',
            'descripcio' => 'Compte per a operacions del dia a dia',
            'imatge' => 'imatges/CuentaTipoCorriente.png'
        ]);

        TipusCompte::firstOrCreate([
            'nom' => 'Estalvi',
            'descripcio' => 'Compte per a estalviar diners',
            'imatge' => 'imatges/CuentaTipoAhorros.png'
        ]);

        TipusCompte::firstOrCreate([
            'nom' => 'Inversions',
            'descripcio' => 'Compte per a inversions financeres',
            'imatge' => 'imatges/CuentaTipoInversiones.png'
        ]);
    }
}
