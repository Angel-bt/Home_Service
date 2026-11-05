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

        $user->deleteProfilePhoto();
        $user->tokens->each->delete();
        $user->delete();
    }
}
