<?php 

return array(

	'authorizeExternalIP' => true,

	'ffmpegPath' => '/usr/bin/ffmpeg',
	'ffprobePath' => '/usr/bin/ffprobe',

	'convertTo' => 'mp4',
	'thumbnailType' => 'jpg',

	'thumbnailPath' => 'cdn/thumbnails/',
	'videoPath' => 'cdn/videos/',

	'rules' => array(
		"url" => array('url', 'regex:/(?i:^.*\.(mp4|avi|wmv|flv|mpg|mov|mkv)$)/')
	)

);