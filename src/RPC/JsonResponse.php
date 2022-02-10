<?php

// Copyright 2019-2021 The Inescoin developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Inescoin\RPC;

use React\Http\Response as HttpResponse;
use Psr\Http\Message\ResponseInterface;

use React\Http\Io\HttpBodyStream;
use RingCentral\Psr7\Response as Psr7Response;

class JsonResponse extends Psr7Response
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
