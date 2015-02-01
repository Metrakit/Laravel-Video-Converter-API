@extends('layouts.master')

@section('js')

  <script>

    function generateKey(nb)
    {
        var text = "";
        var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i=0; i < nb; i++) {
          text += chars.charAt(Math.floor(Math.random() * chars.length));
        }

        return text;
    }

    function generateList(videos)
    {
      var videosJSON = [];

      for(var i = 0; i < videos.length; i++){

        videosJSON.push({
          "url": videos[i],
          "key": generateKey(5)
        });
      }

      convertVideos(videosJSON);

    }

    function convertVideos(videos)
    {
      if (videos.length >= 1) {

        $.post("{{ URL::route('video.store') }}", videos[0], function(data) {

          console.log(videos[0]);
          videos.splice(0,1);

          // next video
          convertVideos(videos);

        }.bind(videos));

      } else {
        console.log('finish !');
      }
    }

    $(document).ready(function() {

        $(".video-links").find('button').click(function() {

          var videoInput = $(".video-links").find('textarea');
          var videos = videoInput.val().split('\n');

          if (videos.length == 0 || videos[0] == "") {
            var formGroup = videoInput.parent().parent();
            if (!formGroup.hasClass('has-error')) {
              formGroup.addClass('has-error');
              videoInput.after('<p class="text-danger">Vous devez ajouter des URL valides</p>');           
            }
          } else {

            $(".video-links").hide();

            generateList(videos);

          }
          
        });

    });


  </script>

@stop


@section('container')


<div class="container">

  <h1>Uploader des vidéos</h1>

  <div class="video-list">

  </div>

  <form class="video-links form-horizontal">

    <div class="form-group">
      <label class="control-label col-md-2">Liens des vidéos</label>
      <div class="col-md-6">
        <textarea class="form-control" name="links" placeholder="http://example/video.wmv"></textarea>
      </div>
    </div>

    <div class="col-md-offset-2 col-md-6">
      <button class="btn btn-primary">Envoyer</button>
    </div>

  </form>

</div>


@stop