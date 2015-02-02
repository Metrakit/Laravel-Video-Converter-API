<?php

class VideoConverterController extends Controller {

	public function __construct()
	{
		header("Access-Control-Allow-Origin: *");
		set_time_limit(0);
	}

	/*
	 * Store new video
	 */
	public function store()
	{

		if (!Input::has('url')) {
			return Response::json(array(
				'message' 	=> "No URL"),
				500
			);
		}

		$validator = Validator::make(Input::all(), Config::get('videoConverter::VideoSettings.rules'));

		if ($validator->fails()) {
			return Response::json(array(
				'message' 	=> "Invalid URL"),
				500
			);
		}

		$fileInfo = pathinfo(Input::get('url'));

		if (sizeof($fileInfo) == 0) {
			return Response::json(array(
				'message' 	=> "Error when parsing URL"),
				500
			);			
		}

		if (file_exists(public_path() . '/cdn/' . $fileInfo['filename'] . '.mp4')) {
			$filename = str_random(5) . '-' . $fileInfo['filename'];
		} else {
			$filename = $fileInfo['filename'];
		}

		$ffmpeg = \FFMpeg\FFMpeg::create(array(
			// Paths to set for the librairies
		    'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
		    'ffprobe.binaries' => '/usr/bin/ffprobe',
		));

		// https://archive.org/download/Tg.flv_865/Tg.flv
		$video = $ffmpeg->open(Input::get('url'));

		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(5))
		    ->save('cdn/' . $filename . '_1.jpg');
		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
		    ->save('cdn/' . $filename . '_2.jpg');
		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(15))
		    ->save('cdn/' . $filename . '_3.jpg');

		$video
		    ->save(new FFMpeg\Format\Video\X264(), 'cdn/' . $filename . '.mp4');

		return Response::json(array(
			'success' => true,
			'data' 	=> Input::get('url')),
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