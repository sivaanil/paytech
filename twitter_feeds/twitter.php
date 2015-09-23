<?php

# Class Twitter
#
# This class is designed to draw a feed from twitter via a twitter ID and save
# it in the database
#
# Author: Marc Steven Plotz (http://www.marcplotz.co.za :: marc@marcplotz.co.za)
# Date: 31/12/2010
# Version: 1.0
# Licence: Use and Abuse at will

# -----------------------------------------------------------------------------#
# Class Twitter
# -----------------------------------------------------------------------------#
class twitter
{
	# The ID of the twitter feed we are trying to read
	public $twitter_id;

	# -----------------------------------------------------------------------------#
	# Get the twitter feed via CURL according to the Twitter ID
	# -----------------------------------------------------------------------------#
	public function twitter_status($twitter_id)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, "http://twitter.com/statuses/user_timeline/".$twitter_id.".xml");
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		$response = curl_exec($c);
		$responseInfo = curl_getinfo($c);
		curl_close($c);

		return $response;
	}

	# -----------------------------------------------------------------------------#
	# This is the mthod we will call, which sets everything up and calls the
	# twitter_status method
	# -----------------------------------------------------------------------------#
	function get_tweets($twitter_id)
	{
		$result = null;
		$i = null;

		# get the actual feed
		$twitter_xml = self::twitter_status($twitter_id);

		# return the RSS Feed as an XML String
		$xml = simplexml_load_string($twitter_xml);

		# Make sure we have something to work with
		if(!empty($xml))
		{
			# loop through each status
			foreach($xml->status as $status)
			{
				# The unique ID of the twitter post
				# We sav e this in the database to make sure that we do not duplicate entries
				$id = $status->id;

				# The actual text of the status string - we run a safety precaution here too
				$content = mysql_real_escape_string($status->text);

				# The time that the post was tweeted
				$created_1 = $status->created_at;

				# let's get a nicely formatted date
				# Split the time up and make it look pretty
				$created_2 = explode(' ', $created_1);

				$day = $created_2[2];
				$month = $this->get_month_num($created_2[1]);
				$year = $created_2[5];
				$time = $created_2[3];

				$times = explode(':', $time);
				$hour = $times[0];

				if(strlen($hour)=='1')
					$hour = '0'.$hour;

				$minute = $times[1];

				if(strlen($minute)=='1')
					$minute = '0'.$minute;

				$second = $times[2];

				if(strlen($second)=='1')
					$second = '0'.$second;

				# Twitter seems to give us a GMT time - I added 2 hours on here to allow for CAT.
				$timestamp = mktime($hour+2, $minute, $second, $month, $day, $year);


				echo "<p>".$content."</p>";

				# -----------------------------------------------------------------------------#
				# This is where you would check if the satus ID is in the database, and
				# update the DB with the new status if need be
				# -----------------------------------------------------------------------------#

			}
			return true;
		}
	return false;
	}

	# -----------------------------------------------------------------------------#
	# Get the numerical value of the month
	# -----------------------------------------------------------------------------#

	public function get_month_num($month)
	{
		if (empty ($month))
			return 'Unknown';

		$months = array ('Jan' => '01', 'Feb' => '02', 'Mar' => '03', 'Apr' => '04', 'May' => '05', 'Jun' => '06', 'Jul' => '07', 'Aug' => '08', 'Sep' => '09', 'Oct' => '10', 'Nov' => '11', 'Dec' => '12');

		return $months[$month];
	}
}
