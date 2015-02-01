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
				'success' => false,
				'data' 	=> "No URL"),
				200
			);
		}

		$validator = Validator::make(Input::all(), Config::get('videoConverter::VideoSettings.rules'));

		if ($validator->fails()) {
			return Response::json(array(
				'success' => false,
				'data' 	=> "Invalid URL"),
				200
			);
		}

		$ffmpeg = FFMpeg\FFMpeg::create();

		// https://archive.org/download/Tg.flv_865/Tg.flv
		$video = $ffmpeg->open(Input::get('url'));

		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(5))
		    ->save('frame1.jpg');
		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
		    ->save('frame2.jpg');
		$video
		    ->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(15))
		    ->save('frame3.jpg');

		$video
		    ->save(new FFMpeg\Format\Video\X264(), 'export-x264.mp4');

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