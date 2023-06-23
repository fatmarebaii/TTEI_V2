
function fetchData(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        var response = JSON.parse(xhr.responseText);
        callback(response);
      }
    };
    xhr.open('GET', url, true);
    xhr.send();
  }

//   const urlParams = new URLSearchParams(window.location.search);
//   const codeLine = urlParams.get('code-line');
  
  // Récupérer et afficher le contenu de 'line_desc' dans l'élément avec l'ID 'prodline'
  fetchData('http://localhost/ttei/dashboard/api/prodline/prodline/?code-line=069', function(response) {

    var prodlineElement = document.getElementById('prodline');
    prodlineElement.textContent = 'PRODLINE: ' + response.line_desc;
    
  });
  
  // Récupérer et afficher le contenu de 'latest_reference' dans l'élément avec l'ID 'ref'
  fetchData('http://localhost/ttei/dashboard/api/prodline/prod-reference/?code-line=069', function(response) {

    var refElement = document.getElementById('ref');
    refElement.textContent = response.latest_reference;

    var okrefElement = document.getElementById('okref');
    okrefElement.textContent = "REF: "+response.qOK;
  
    var nokrefElement = document.getElementById('nokref');
    nokrefElement.textContent = "REF: "+ response.qNOK;
  
    var ppmrefElement = document.getElementById('ppmref');
    ppmrefElement.textContent = "REF: "+response.qPPM;
    
  });
  
  // Récupérer et afficher le contenu de 'OK', 'NOK' et 'PPM' dans les éléments correspondants
  fetchData('http://localhost/ttei/dashboard/api/prodline/prod-shift/?code-line=069', function(response) {
    var okshiftElement = document.getElementById('okshift');
    okshiftElement.textContent = "SHIFT: "+response.qOK;
  
    var nokshiftElement = document.getElementById('nokshift');
    nokshiftElement.textContent = "SHIFT: "+ response.qNOK;
  
    var ppmshiftElement = document.getElementById('ppmshift');
    ppmshiftElement.textContent = "SHIFT: "+response.qPPM;
  });
  
  // Récupérer et afficher le contenu de 'operator_count' dans l'élément avec l'ID 'présence'
  fetchData('http://localhost/ttei/dashboard/api/prodline/operator/?code-line=069', function(response) {
    var présenceElement = document.getElementById('presence');
    présenceElement.textContent = response.operator_count;
  });
  