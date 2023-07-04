function fetchData(url) {
  return new Promise(function(resolve, reject) {
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if (xhr.status === 200) {
          resolve(JSON.parse(xhr.responseText));
        } else {
          reject(xhr.status);
        }
      }
    };
    xhr.open('GET', url, true);
    xhr.send();
  });
}

const urlParams = new URLSearchParams(window.location.search);
const codeLine = urlParams.get('code-line');

// Récupérer et afficher le contenu de 'line_desc' dans l'élément avec l'ID 'prodline'
function fetchAndDisplayProdLineData() {
  fetchData('http://127.0.0.1/ttei/dashboard/api/prodline/prodline/?code-line=' + codeLine)
    .then(function(response) {
      var prodlineElement = document.getElementById('prodline');
      prodlineElement.textContent = 'PRODLINE: ' + response.line_desc;
    })
    .catch(function(error) {
      console.log('Une erreur s\'est produite lors de la récupération des données de prodline:', error);
    });
}

// Récupérer et afficher le contenu de 'latest_reference' dans l'élément avec l'ID 'ref'
function fetchAndDisplayProdReferenceData() {
  fetchData('http://127.0.0.1/ttei/dashboard/api/prodline/prod-reference/?code-line=' + codeLine)
    .then(function(response) {
      var refElement = document.getElementById('ref');
      if (response.latest_reference === '') {
        refElement.textContent = 'NO REFERENCE AVAILABLE';
      } else {
        refElement.textContent = response.latest_reference;
      }

      var okrefElement = document.getElementById('okref');
      okrefElement.textContent = 'REF: ' + response.qOK;

      var nokrefElement = document.getElementById('nokref');
      nokrefElement.textContent = 'REF: ' + response.qNOK;

      var ppmrefElement = document.getElementById('ppmref');
      ppmrefElement.textContent = 'REF: ' + response.qPPM;
    })
    .catch(function(error) {
      console.log('Une erreur s\'est produite lors de la récupération des données de prod-reference:', error);
    });
}


// Récupérer et afficher le contenu de 'OK', 'NOK' et 'PPM' dans les éléments correspondants
function fetchAndDisplayProdShiftData() {
  fetchData('http://127.0.0.1/ttei/dashboard/api/prodline/prod-shift/?code-line=' + codeLine)
    .then(function(response) {

      var okshiftElement = document.getElementById('okshift');
      okshiftElement.textContent = 'SHIFT: ' + response.qOK;

      var nokshiftElement = document.getElementById('nokshift');
      nokshiftElement.textContent = 'SHIFT: ' + response.qNOK;

      var ppmshiftElement = document.getElementById('ppmshift');
      ppmshiftElement.textContent = 'SHIFT: ' + response.qPPM;
    })
    .catch(function(error) {
      console.log('Une erreur s\'est produite lors de la récupération des données de prod-shift:', error);
    });
}

// Récupérer et afficher le contenu de 'operator_count' dans l'élément avec l'ID 'présence'
function fetchAndDisplayOperatorData() {
  fetchData('http://127.0.0.1/ttei/dashboard/api/prodline/operator/?code-line=' + codeLine)
    .then(function(response) {
      var présenceElement = document.getElementById('presence');
      présenceElement.textContent = response.operator_count;
    })
    .catch(function(error) {
      console.log('Une erreur s\'est produite lors de la récupération des données de operator:', error);
    });
}

// Récupérer et afficher le contenu de 'productivity' dans l'élément avec l'ID 'productivity'
function fetchAndDisplayProductivityData() {
  fetchData('http://127.0.0.1/ttei/dashboard/api/prodline/productivity/?code-line=' + codeLine)
    .then(function(response) {
      var productivityElement = document.getElementById('productivity');
      productivityElement.textContent = response.productivity;
      console.log(response.productivity);
    })
    .catch(function(error) {
      console.log('Une erreur s\'est produite lors de la récupération des données de productivity:', error);
    });
}

// Fonction pour l'auto-actualisation toutes les 5 secondes
function autoRefresh() {
  fetchAndDisplayProdLineData();
  fetchAndDisplayProdReferenceData();
  fetchAndDisplayProdShiftData();
  fetchAndDisplayOperatorData();
  fetchAndDisplayProductivityData();
}

// Appeler la fonction d'auto-actualisation toutes les 5 secondes
setInterval(autoRefresh, 5000);
