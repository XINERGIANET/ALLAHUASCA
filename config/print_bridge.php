<?php

return [

    /*
     * Cola persistente en base de datos para comandas hacia otra PC.
     * Una reserva evita dobles impresiones simultáneas, pero el trabajo permanece Pendiente hasta el ack.
     */

    'claim_timeout_seconds' => env('PRINT_BRIDGE_CLAIM_TIMEOUT_SECONDS', 3),

];
