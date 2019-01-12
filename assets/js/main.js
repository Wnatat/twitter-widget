var Updater = function() {
  this.max_id;
  this.fail = false;

  this.isUpdated = function(data) {
    return data.length > 0;
  };

  this.setMaxId = function(data) {
    if(data[0].id == this.max_id) {
      data.pop();
    } else {
      this.max_id = data[0].id;
    }
  };

  this.removeBlueBackground = function(elements) {  
    setTimeout(function() {
      if(typeof elements !== 'undefined') {
        elements.removeClass("injected");
      }
    }, 3000);
  };

  this.params = function() {
   return ((typeof this.max_id) != 'undefined') ? { since_id: this.max_id } : {};
  };

  this.setFail = function(fail) {
    this.fail = fail;
  };

  this.simulate = function(resolve, reject) {
    var rnd = Math.floor(Math.random() * Math.floor(10));

    setTimeout(function() {
      if(this.fail) {
        return reject({ errors: { msg: 'Not working' } })
      } else {
        return resolve(data.slice(rnd, rnd + rnd));
      }
    }, 600);
  };
}

Updater.prototype.toHtml = function(tweet) {
  var noImage = tweet.medias.length > 0 ? "" : " no-image";

  var medias = '';

  if(tweet.medias.length > 0) {
    medias +=  "  <a href=\"" + tweet.user.url + "\">" + 
               "    <img src=\"" + tweet.medias[0] + "\" />" + 
               "  </a>";
  }

  return "<article class=\"card" + noImage + " injected\">" +
            medias + 
         "  <div class=\"tweet\">" + 
         "    <h3>" + 
         "      <i class=\"twitter-icon " + noImage + "\"></i>" + 
                tweet.user.name + 
         "      <small>" +
         "        <a href=\"" + tweet.user.url + "\">" +
         "          @" + tweet.user.screen_name +
         "        </a>" + 
         "      </small>" +
         "    </h3>" + 
         "    <div class=\"description\">" + tweet.text + "</div>" +
         "  </div>" +
         "</article>";
};

Updater.prototype.render = function(data) {
  that = this;

  return data.map(function(tweet) {
    return that.toHtml(tweet);
  });
};

Updater.prototype.success = function(data) {
  console.log(data.length);

  if(this.isUpdated(data)) {
    this.setMaxId(data);
  }

  $(".loader").addClass('collapsed');
  $('section article').slice(10).remove();
  $("section div:first").after(this.render(data));

  this.removeBlueBackground($("article.injected"));
};

Updater.prototype.error = function(xhr, status, error) {
  $("section > div")
    .html("<div>" + 
            "<img src=\"/assets/images/error.svg\" class=\"error\" />" +
            "<h2 class=\"error\">Error: " + error + "</h2>" +
          "</div>");
};

Updater.prototype.refresh = function(callback) {
  $(".loader").removeClass('collapsed');
  
  $("section > div")
    .html("<div>" + 
            "<img src=\"assets/images/loading.svg\" />" +
            "<h2>Refreshing tweets...</h2>" + 
          "</div>");

  var animation_timeout = setTimeout(function() {
    callback(this.success, this.error, (Math.random() >= 0.7));
  }, 300);
};

Updater.prototype.fetch = function(success, error) {
  $.get("http://localhost:8080/api.php", this.params())
    .done(this.success.bind(this))
    .fail(this.error.bind(this));
};

Updater.prototype.dry_run = function(success, error, fail) {
  this.setFail(fail);

  var promise = new Promise(this.simulate);

  return promise.then(this.success.bind(this), this.error.bind(this));
};