<?php
if( !class_exists('Stripe_ApiConnectionError')) {
	class Stripe_ApiConnectionError extends Stripe_Error
	{
	}
}