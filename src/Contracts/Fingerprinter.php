<?php

namespace BoxedCode\Laravel\Auth\Device\Contracts;

use Illuminate\Http\Request;

interface Fingerprinter
{
    /**
     * Fingerprint a request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return string
     */
    public function fingerprint(Request $request);
}
