/* Alternar entre disposição em carrossel e disposição em cartolinas.*/
function changeLayout(event) {
    // Botão que despoletou o evento
    var pressedButton = event.currentTarget;
    // Menu em mosaico. Se for nulo, está em modo carrossel
    var menu = document.querySelector('.tileMenu');

    if(menu) {
        // Passar para carrossel; novo clique no botão volta a mosaico
        menu.className = 'menu carouselMenu';
        pressedButton.innerText = 'Mostrar mosaicos';
    } else {
        // E vice-versa
        menu = document.querySelector('.carouselMenu');
        menu.className = 'menu tileMenu';
        pressedButton.innerText = 'Mostrar carrossel';
    }
}

/* Mudar para outra opção no menu de navegação em carrossel.
 *
 * - Se o argumento `avance` for positivo, avança para a direita;
 * - Se for negativo, recua para a esquerda;
 * - Se for zero, não muda.
 *
 * A função tem em conta que o último elemento são os botões de navegação
*/
function swipeMenu(event, avance) {
    var menu = document.querySelector('.carouselMenu');
    if(!menu) {
        console.log('AVISO! Está em falta menu de carrossel');
        return;
    }

    // Obter primeiro e última opções do menu, assim como aquela
    // que ainda está selecionada (antes de mudar a opção)
    var first = menu.firstElementChild;
    var last = menu.lastElementChild.previousElementSibling;
    var current = document.querySelector('.menuItem.activeMenuItem');

    // Declarar variável para a opção que fica selecionada no fim da função
    var upcoming;

    if(avance > 0) {

        if(current !== last) {
            upcoming = current.nextElementSibling;
        } else {
            upcoming = first;
        }

    } else if(avance < 0) {

        if(current !== first) {
            upcoming = current.previousElementSibling;
        } else {
            upcoming = last;
        }

    } else {
        return;
    }

    current.className = "menuItem";
    upcoming.className = "activeMenuItem menuItem";
}

