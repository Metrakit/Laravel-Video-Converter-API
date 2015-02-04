@extends('layouts.master')

@section('js')


  <style>

    .video-list {
      margin: 20px 0px 0px 20px;
    }

    .video-list img {
      max-width: 200px;
      max-height: 200px;
    }

  </style>

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

      for (var i = 0; i < videos.length; i++) {
        var key = generateKey(5);
        videosJSON.push({
          "url": videos[i],
          "key": key
        });

        $('.video-list').append('<div id="video-' + key + '"><p><i class="fa"></i> Video from: ' + videos[i] + '</p></div>')
      }

      convertVideos(videosJSON);

    }

    function convertVideos(videos)
    {
      if (videos.length >= 1) {

        var content = $('#video-' + videos[0].key);
        var icon = content.find('i');

        icon.addClass('fa-spinner fa-spin');

        $.post("{{ URL::route('video.store') }}", videos[0])

          .done(function(data) {

            icon.addClass('fa-check text-success');
            content.find('p').append(' <span class="text-success">(Exécuté en ' + data.time + ' minutes)</span>');
            content.append('
              <div class="row">
                <div class="col-md-4">
                  <img src="/cdn/thumbnails/' + data.fileName + '_1.jpg" />
                </div>
              <div class="col-md-4">
                <img src="/cdn/thumbnails/' + data.fileName + '_2.jpg" />
              </div>
              <div class="col-md-4">
                <img src="/cdn/thumbnails/' + data.fileName + '_3.jpg" />
              </div>
            </div>');
          
          }.bind(videos))

          .fail(function(data) {
            console.log(data);
            icon.addClass('fa-times text-danger');    
            content.find('p').append(' <span class="text-danger">(' + data.responseJSON.message + ')</span>');
          })

          .always(function() {
            icon.removeClass('fa-spinner fa-spin');
            videos.splice(0,1);
            convertVideos(videos);            
          });

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

  <form class="video-links form-horizontal" action="javascript:void(0);">

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