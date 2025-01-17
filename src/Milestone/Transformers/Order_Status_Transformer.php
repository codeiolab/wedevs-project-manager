<?php

namespace WeDevs\PM\Milestone\Transformers;

use League\Fractal\TransformerAbstract;
use WeDevs\PM\Common\Traits\Resource_Editors;

class Order_Status_Transformer extends TransformerAbstract{
    use Resource_Editors;
    
    public function transform(  $item ) {
        return [
            'data'=> $item,
        ];
    }
}