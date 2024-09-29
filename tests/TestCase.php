<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

	/**
	 * Decode Response
	 */
	protected function content($response, $asArray = true)
	{
		return json_decode($response->content(), !!$asArray) ?? $response->content();
	}

	/**
	 * Get Original Response
	 */
	protected function original($response)
	{
		$original = $response->original;
		return ($original instanceof ModelResource) ? $original->resource : $original;
	}
}
