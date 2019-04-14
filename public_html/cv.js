/* Mudar visibilidade de uma seccao do CV. Também muda o texto
 * no botão para refletir a ação que irá realizar da próxima vez
 * que for carregado. Ou seja, o texto no botão é "Mostrar" 
 * quando o conteúdo está escondido e "Esconder" quando está 
 * visível.
 *
 * Parametros:
 *   id - valor do "id" do elemento a esconder/mostrar
 *   event - evento do rato despoletado por carregar no botão;
 *           é necessário para identificar o botão
 * 
 */
function mudarVisibilidadeCv(event, id) {
    /* Determinar se está escondido */
    var elemSeccao = document.getElementById(id);
    var escondido = (elemSeccao.style.display === "none");

    /* Mudar etiqueta no botão */
    var elemBotao = event.currentTarget;
    var novoTexto = escondido ? "Esconder" : "Mostrar";
    elemBotao.innerHTML = novoTexto;

    /* Esconder / mostrar a secção */
    var novoValor = escondido ? "unset" : "none";
    elemSeccao.style.display = novoValor;
}
