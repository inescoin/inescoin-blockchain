<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use React\Http\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;

class JsonResponse extends HttpResponse implements ResponseInterface
{
    public function __construct($data)
    {
        parent::__construct(
        	200,
        	[ 'Content-Type' => 'application/json' ],
        	json_encode($data)
        );
    }
}
