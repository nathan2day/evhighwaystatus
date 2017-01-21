<?php

namespace App\Transformers;

class ConnectorTransformer extends Transformer
{
	public function transform($connector)
	{
		return [
			'unique'		=> $connector->id,
			'type'			=> [
				'title' 	=> count($connector->type) ? $connector->type[0]->name : 'unknown',
				'id'		=> count($connector->type) ? $connector->type[0]->id : 0,
			],
            'power'			=> $connector->power,
            'quantity'		=> 1, // TODO
			'status'		=> $connector->status,
		];
	}
}
