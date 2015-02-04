<?php

class VideoConverterController extends Controller {

	public function __construct()
	{
		if (Config::get('videoConverter::VideoSettings.authorizeExternalIP')) {
			header("Access-Control-Allow-Origin: *");
		}
		
		// Unlimited time for can convert videos
		set_time_limit(0);
	}

	/*
	 * Store new video
	 * @return Objec Response
	 */
	public function store()
	{

		$execStart = microtime(true);

		if (!Input::has('url')) {
			// return error: no URL
			return Response::json(array(
				'message' 	=> Lang::get('videoConverter::message.error.noUrl')),
				500
			);
		}

		$validator = Validator::make(Input::all(), Config::get('videoConverter::VideoSettings.rules'));

		if ($validator->fails()) {
			// return error: invalid URL
			return Response::json(array(
				'message' 	=> Lang::get('videoConverter::message.error.invalidUrl')),
				500
			);
		}

		$fileInfo = pathinfo(Input::get('url'));

		if (sizeof($fileInfo) == 0) {
			// return error: parsing URL
			return Response::json(array(
				'message' 	=> Lang::get('videoConverter::message.error.parsingUrl')),
				500
			);			
		}

		// If the video exist we rename the filename
		if (file_exists(Config::get('videoConverter::VideoSettings.videoPath') .  $fileInfo['filename'] . '.' . Config::get('videoConverter::VideoSettings.convertTo'))) {
			$filename = str_random(5) . '-' . $fileInfo['filename'];
		} else {
			$filename = $fileInfo['filename'];
		}

		$ffmpeg = FFMpeg\FFMpeg::create(array(
			// Paths to set for the librairies
		    'ffmpeg.binaries'  => Config::get('videoConverter::VideoSettings.ffmpegPath'),
		    'ffprobe.binaries' => Config::get('videoConverter::VideoSettings.ffprobePath'),
		    'timeout'          => 0
		));

		// example: https://archive.org/download/Tg.flv_865/Tg.flv
		// https://archive.org/download/11Wmv/Produce_1.wmv
		// try : https://archive.org/download/avi/avicompleet.mov
		// https://archive.org/download/22avi/22Avi.avi
		$video = $ffmpeg->open(Input::get('url'));

		$videoStream = FFMpeg\FFProbe::create();
		/*$videoStream
			->streams(Input::get('url'))
			->videos() 
			->first();*/

		$duration = $videoStream->format(Input::get('url'))
    					->get('duration');  	

    	$nbThumbs = Config::get('videoConverter::VideoSettings.nbThumbnails');

    	$frameTime = 0;
    	$timeByThumb = floor($duration) / $nbThumbs;

    	for ($i=0; $i < $nbThumbs ; $i++) { 
			if ($i == 0) {
				// 5 seconds in more for the first frame
				$frameTime = 5;
			} else if ($i == $nbThumbs -1) {
				// 10 seconds in less for the last frame
				$frameTime = ($frameTime + $timeByThumb) - 10;
			} else {
				$frameTime = $frameTime + $timeByThumb;
			}

			$frameNumber = $i + 1;

			// Generate frame
			$video
		    	->frame(FFMpeg\Coordinate\TimeCode::fromSeconds($frameTime))
		    	->save(Config::get('videoConverter::VideoSettings.thumbnailPath') . $filename . '_' . $frameNumber . '.' . Config::get('videoConverter::VideoSettings.thumbnailType'));		
		}		

		// Generate video
		$video
		    ->save(new FFMpeg\Format\Video\X264(), Config::get('videoConverter::VideoSettings.videoPath') . $filename . '.' . Config::get('videoConverter::VideoSettings.convertTo'));


		$execEnd = microtime(true);    

		// Time in minutes
		$timeExec = round(($execEnd - $execStart)  / 60);

		return Response::json(array(
			'success' 	=> true,
			'data' 		=> Input::get('url'),
			'fileName' 	=> $filename,
			'duration'	=> $duration,
			'nbThumbs'	=> $nbThumbs,
			'time'		=> $timeExec),
			200
		);

	}

	/**
	 * Show homepage
	 * @return View
	 */
	public function show()
	{
		return View::make('videoConverter::show');
	}
}