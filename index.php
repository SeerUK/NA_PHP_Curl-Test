<?php

	/* Set the website timezone to UTC if no timezone is specified in the
	 * php.ini file.
	 * ================================================================== */
	if ( function_exists( 'date_default_timezone_set' ) )
	{
		if ( ! @date_default_timezone_get() )
		{
			date_default_timezone_set( @ini_get( 'date.timezone' ) ? ini_get( 'date.timezone' ) : 'UTC' );
		}
		else
		{
			date_default_timezone_set( 'UTC' );
		}
	}

	$strRoot = 'https://api.github.com';
	$strURI  = '/users/SeerUK/events';
	$objCurl = curl_init();

	curl_setopt( $objCurl, CURLOPT_URL, $strRoot . $strURI );
	curl_setopt( $objCurl, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $objCurl, CURLOPT_SSL_VERIFYPEER, false );

	// curl_setopt( $objCurl, CONNECTTIMEOUT, 1 );
	$strResponse = curl_exec( $objCurl );
	$objResponse = json_decode( $strResponse );
	$arrFeed	 = array();

	$i = 0;
	foreach( $objResponse as $objItem )
	{
		$arrFeed[$i]['actor']['name']	= $objItem->actor->login;
		$arrFeed[$i]['actor']['avatar']	= $objItem->actor->avatar_url;
		$arrFeed[$i]['actor']['url']	= $objItem->actor->url;

		$arrFeed[$i]['repo']['name']	= $objItem->repo->name;
		$arrFeed[$i]['repo']['url']		= $objItem->repo->url;

		$arrFeed[$i]['created_at']		= $objItem->created_at;

		/* Begin Friendly Output:
		 * ====================== */
		$arrFeed[$i]['details'] = '<a target="_blank" href="https://github.com/' . $objItem->actor->login . '">' . $objItem->actor->login . '</a> ';

		/* Handle different events from Github:
		 * ==================================== */
		switch ( $objItem->type ) 
		{
			/* Creating Branches / Repositories:
			 * ================================= */
			case 'CreateEvent':
				switch ($objItem->payload->ref_type) 
				{
					case 'branch':
						$arrFeed[$i]['details'].= 'created ' . $objItem->payload->ref_type . ' <a target="_blank" href="https://github.com/' . $objItem->repo->name . '/tree/' . $objItem->payload->ref . '">' 
						                                     . $objItem->payload->ref . '</a> in <a target="_blank" href="https://github.com/' . $objItem->repo->name . '">' . $objItem->repo->name . '</a>';
						break;
					case 'repository':
						$arrFeed[$i]['details'].= 'created ' . $objItem->payload->ref_type . ' <a target="_blank" href="https://github.com/' . $objItem->repo->name . '">' . $objItem->repo->name . '</a>';
						break;
					default:
						$arrFeed[$i]['details'].= 'created a ' . $objItem->payload->ref_type;
						break;
				}
				break;

			/* Creating / Editing Gists:
			 * ========================= */
			case 'GistEvent':
				$arrFeed[$i]['details'].= 'created gist <a target="_blank" href="' . $objItem->payload->gist->html_url . '">' . $objItem->payload->gist->html_url . '</a>';
				break;

			/* Commenting on an 'Issue':
			 * ========================= */
			case 'IssueCommentEvent':
				$arrFeed[$i]['details'].= 'commented on ';
				break;

			/* Pushing to a Branch -> Repository:
			 * ================================== */
			case 'PushEvent':
				$arrFeed[$i]['details'].= 'pushed to';
				break;

			/* Unhandled events:
			 * ================= */
			default:
				$arrFeed[$i]['details'].= 'was active in';
				break;
		}

		$arrFeed[$i]['time'] = strtotime( $arrFeed[$i]['created_at'] );



		$i = $i + 1;
	}

	foreach( $arrFeed as $arrItem )
	{
		echo $arrItem['details'];
		echo '<br />';
		echo $arrItem['time'];
		echo "<br /><br />";
	}

	//var_dump( $arrFeed );

	var_dump( $objResponse );