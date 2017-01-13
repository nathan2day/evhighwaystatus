<?php

namespace App\Transformers;

class HistoryTransformer extends Transformer
{
	public function transform($history)
	{
		return [
			'type'		=> $history->connector->name,
			'date_time'	=> $history->updated_at,
			'new_status'=> $history->new,
		];
	}
}