// Connexion
if(document.getElementById('connect') != null){
  document.getElementById('connect').onclick = function(){
    document.getElementById('connectBox').style.display = 'block';
  }

  document.onclick = function(e){
    if(e.target.id != 'connectBox'
    && e.target.id != 'connect'
    && e.target.id != 'pseudo'
    && e.target.id != 'password'
    && e.target.id != 'btnConnect')
    document.getElementById('connectBox').style.display = 'none';
  }
}

// Flash msg
if(document.getElementById('success') != null){
  setTimeout(function(){
      document.getElementById('success').style.display = 'none';
    },
    3000
  );

  document.getElementById('success').onclick = function(){
    document.getElementById('success').style.display = 'none';
  }
}

if(document.getElementById('error') != null){
  setTimeout(function(){
      document.getElementById('error').style.display = 'none';
    },
    3000
  );

  document.getElementById('error').onclick = function(){
    document.getElementById('error').style.display = 'none';
  }
}
