<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Http\Request;

interface Fingerprinter
{
    public function fingerprint(Request $request);
}