<?php

namespace App\Traits;

trait BypassLicenseCheck
{
    /**
     * Override the license check to always return true
     * This bypasses the Envato purchase code validation
     */
    public function isLegal()
    {
        return true;
    }
}
