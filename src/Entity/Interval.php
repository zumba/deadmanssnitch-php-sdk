<?php

namespace Zumba\Deadmanssnitch\Entity;

final class Interval
{
	const I_15MINUTE = '15_minute';
	const I_30MINUTE = '30_minute';
	const I_HOURLY = 'hourly';
	const I_DAILY = 'daily';
	const I_WEEKLY = 'weekly';
	const I_MONTHLY = 'monthly';

	private $available = [
		self::I_15MINUTE,
		self::I_30MINUTE,
		self::I_HOURLY,
		self::I_DAILY,
		self::I_WEEKLY,
		self::I_MONTHLY,
	];

	private $value;

	public function __construct($value)
	{
		if (!in_array($value, $this->available)) {
			throw new \InvalidArgumentException('Not a valid interval');
		}
		$this->value = $value;
	}

	public function __toString()
	{
		return $this->value;
	}
}