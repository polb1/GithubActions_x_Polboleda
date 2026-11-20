<?php

use App\Models\Bizum;
use App\Models\Client;
use App\Models\Compte;
use App\Models\TipusCompte;
use App\Models\User;

// Crea un usuari amb client i un compte amb el saldo indicat
function crearClientAmbCompte(string $dni, string $iban, float $saldo): array
{
    $user = User::factory()->create();
    $client = Client::create([
        'user_id' => $user->id,
        'dni' => $dni,
        'data_naixament' => '2000-01-01',
        'telefon' => '600000000',
    ]);
    $tipus = TipusCompte::firstOrCreate(['nom' => 'Corrent']);
    $compte = Compte::create([
        'iban' => $iban,
        'alias' => 'Compte',
        'saldo' => $saldo,
        'client_id' => $client->id,
        'tipus_compte_id' => $tipus->id,
    ]);

    return [$user, $compte];
}

test('un client pot enviar un bizum i es mouen els saldos', function () {
    [$user, $origen] = crearClientAmbCompte('11111111A', 'ES0000000000000000000001', 100);
    [, $desti] = crearClientAmbCompte('22222222B', 'ES0000000000000000000002', 0);

    $this->actingAs($user)->post('/bizum', [
        'compte_origen_id' => $origen->id,
        'compte_desti_id' => $desti->id,
        'quantitat' => 30,
    ])->assertRedirect(route('bizum.index'));

    expect((float) $origen->fresh()->saldo)->toBe(70.0);
    expect((float) $desti->fresh()->saldo)->toBe(30.0);
    expect(Bizum::count())->toBe(1);
});

test('no es pot enviar un bizum sense saldo suficient', function () {
    [$user, $origen] = crearClientAmbCompte('11111111A', 'ES0000000000000000000001', 10);
    [, $desti] = crearClientAmbCompte('22222222B', 'ES0000000000000000000002', 0);

    $this->actingAs($user)->post('/bizum', [
        'compte_origen_id' => $origen->id,
        'compte_desti_id' => $desti->id,
        'quantitat' => 50,
    ])->assertSessionHas('error');

    expect((float) $origen->fresh()->saldo)->toBe(10.0);
    expect(Bizum::count())->toBe(0);
});

test('no es pot enviar diners des del compte d\'un altre client', function () {
    [$user] = crearClientAmbCompte('11111111A', 'ES0000000000000000000001', 100);
    [, $aliena] = crearClientAmbCompte('22222222B', 'ES0000000000000000000002', 100);
    [, $desti] = crearClientAmbCompte('33333333C', 'ES0000000000000000000003', 0);

    $this->actingAs($user)->post('/bizum', [
        'compte_origen_id' => $aliena->id,
        'compte_desti_id' => $desti->id,
        'quantitat' => 20,
    ])->assertSessionHas('error');

    expect((float) $aliena->fresh()->saldo)->toBe(100.0);
});

test('un client normal no pot entrar al panell d\'admin', function () {
    [$user] = crearClientAmbCompte('11111111A', 'ES0000000000000000000001', 0);

    $this->actingAs($user)->get('/admin')->assertRedirect(route('noAdmin'));
});

test('els seeders es poden executar dues vegades sense duplicar', function () {
    $this->seed();
    $this->seed();

    expect(App\Models\User::where('email', 'admin@admin.cat')->count())->toBe(1);
    expect(App\Models\TipusCompte::count())->toBe(3);
});
