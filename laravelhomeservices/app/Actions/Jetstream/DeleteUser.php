<?php

namespace App\Actions\Jetstream;

use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     *
     * @param  mixed  $user
     * @return void
     */
    public function delete($user)
    {
        // La base borra las filas en cascada, pero los archivos del disco privado hay que borrarlos aquí.
        if ($user->serviceProvider) {
            app(\App\Services\ProviderVerification::class)->forget($user->serviceProvider);
        }

        // Sin esto sus servicios quedaban publicados sin dueño (services.user_id pasa a null) y se
        // mostraban como si fueran de la administración, con el WhatsApp de Malakplace.
        $cleaner = app(\App\Services\ServiceAssetCleaner::class);
        foreach ($user->services()->get() as $service) {
            $cleaner->deleteFiles($service);
            $service->delete();
        }

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
