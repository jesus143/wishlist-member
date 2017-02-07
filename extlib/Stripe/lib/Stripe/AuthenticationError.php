<?php
if( !class_exists('Stripe_AuthenticationError')) {
	class Stripe_AuthenticationError extends Stripe_Error
	{
	}
}