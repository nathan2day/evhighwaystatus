<?php

namespace App\Transformers;

abstract class Transformer
{
	public function transformCollection($items)
	{
		return array_map([$this,'transform'], $items);
	}

	abstract function transform($item);
}
