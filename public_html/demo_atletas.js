"use strict";

/* Evitar submissão do formulário, o navegador não sair da página */
function naoSubmeter(event) {
    event.preventDefault();
}

/* Obter objeto com valores do formulário
 *
 * Parametro: id - string com o id do formulário
*/
function extrairValoresFormulario(id) {
    if(typeof id !== 'string') {
        throw 'extrairValoresFormulario: Argumento tem que ser string';
    }
    var elForm = document.getElementById(id);
    if(elForm === null) {
        throw 'extrairValoresFormulario: elemento com id #' + id +
            'não encontrado';
    }
    if(elForm.tagName !== 'FORM') {
        throw 'extrairValoresFormulario: elemento com id #' + id +
            'não é um elemento FORM (formulário)';
    }

    var dadosForm = new FormData(elForm);

    var obj = {};
    for(var dado of dadosForm) {
        // Percorrer lista de [name, value]
        //   dos elementos do formulário
        if(dado[0].endsWith('[]')) {
            var nomeProp = dado[0].slice(0, -2);
            obj[nomeProp] = extrairValorCheckbox(dado[0]);
        } else {
            obj[dado[0]] = dado[1];
        }
    }

    return obj;
}


/* Função  "extrairValorCheckbox":
 * Obter array com valores selecionados de conjunto de inputs 
 *   do tipo checkbox.
 *
 * Parametro: name - o valor da propriedade name, que agrupa
 *            as checkboxes
 */
function extrairValorCheckbox(name) {
    var arr = [];
    var elems = document.getElementsByName(name);
    for(var el of elems) {
        if(el.checked) {
            arr.push(el.value);
        }
    }
    return arr;
}

/* Função utilizada na primeira versão da demo dos atletas.
 * Chama, através de AJAX, a versão do script PHP que recebe 
 * e retorna dados em formato JSON; depois trata esses dados 
 * e apresenta-os na página HTML onde é invocada.
 */
function pedirApresentarDadosJson() {

    var objJson = extrairValoresFormulario('formFiltros');

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
         console.log(this.responseText);
        if(this.readyState == 4 && this.status == 200) {
            var objResposta = JSON.parse(this.responseText);
            // console.log(objResposta);
            if(!objResposta.sucesso) {

                console.err(objResposta.erro);
                if(objResposta.erro_stmt) {
                    console.err('SQL statement error:');
                    console.err(objResposta.erro_stmt);
                }
                return;
            }

            // Obter tabela onde se vai apresentar dados
            var tabResultados = document.getElementById('reg-resultados');

            // Remover resultados anteriores
            tabResultados.innerHTML = '';

            // Preencher tabela
            for(var reg of objResposta.registos) {
                var node = document.createElement('tr');
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


