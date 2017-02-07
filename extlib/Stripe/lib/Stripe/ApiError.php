<?php
if( !class_exists('Stripe_ApiError')) {
	class Stripe_ApiError extends Stripe_Error
	{
	}
}
