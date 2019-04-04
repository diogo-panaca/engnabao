"use strict";

function naoSubmeter(event) {
    event.preventDefault();
}

function pedirApresentarDadosJson() {
    // preventDefault();
    var dadosForm = new FormData(
        document.getElementById('formFiltros'));

    var objJson = {};
    for(var dado in dadosForm) {
        // Percorrer lista de [name, value]
        //   dos elementos do formulário
        objJson[dado[0]] = dado[1];
    }

    if(objJson.ano_min === "") {
        objJson.ano_min = 1896;
    }
    if(objJson.ano_max === "") {
        objJson.ano_max = 2999;
    }

    // console.log(objJson);

    var xmlhttp = new XMLHttpRequest();
    var strJson = JSON.stringify(objJson);

    xmlhttp.onreadystatechange = function () {
        // console.log(this.responseText);
        if(this.readyState == 4 && this.status == 200) {
            var objResposta = JSON.parse(this.responseText);
            // console.log(objResposta);
            if(!objResposta.sucesso) {
                return;
            }

            // Obter tabela onde se vai apresentar dados
            var tabResultados = document.getElementById('resultados');

            // Remover resultados anteriores
            for(var node in document.query('.meta-ajax-response')) {
                node.parentNode.removeChild(node);
            }

            for(var reg in objResposta.registos) {
                var node = document.createElement('tr');
                node.class = 'meta-ajax-response';
                node.innerHTML =
                    '<td>' + reg.atleta + '</td>' +
                    '<td>' + reg.modalidade + '</td>' +
                    '<td>' + reg.competicao + '</td>' +
                    '<td>' + reg.edicao + '</td>' +
                    '<td>' + reg.louvor + '</td>';

                tabResultados.appendChild(node);
            }
        }
    }
    xmlhttp.open('POST', 'atletas_json.php', true);
    xmlhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xmlhttp.send('strJson=' + strJson);
    
}
