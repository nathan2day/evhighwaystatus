<?php

namespace App\Transformers;

class HistoryTransformer extends Transformer
{
	public function transform($history)
	{
		return [
			'type'		=> $history->trackable->type->first()->name,
			'date_time'	=> $history->updated_at,
            'old_status'=> $history->old ?: '',
			'new_status'=> $history->new,
		];
	}
}
