<?php

namespace App\Transformers;

class ConnectorTransformer extends Transformer
{
	public function transform($connector)
	{
		return [
			'type'			=> [
				'title' 	=> $connector->name,
				'id'		=> $connector->typeid, // TODO
			],
			'power'			=> $connector->power,
			'quantity'		=> 1, // TODO
			'status'		=> $connector->status,
		];
	}
}
