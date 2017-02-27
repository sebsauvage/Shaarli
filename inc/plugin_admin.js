/**
 * Change the position counter of a row.
 *
 * @param elem  Element Node to change.
 * @param toPos int     New position.
 */
function changePos(elem, toPos)
{
    var elemName = elem.getAttribute('data-line')

    elem.setAttribute('data-order', toPos);
    var hiddenInput = document.querySelector('[name="order_'+ elemName +'"]');
    hiddenInput.setAttribute('value', toPos);
}

/**
 * Move a row up or down.
 *
 * @param pos  Element Node to move.
 * @param move int     Move: +1 (down) or -1 (up)
 */
function changeOrder(pos, move)
{
    var newpos = parseInt(pos) + move;
    var lines = document.querySelectorAll('[data-order="'+ pos +'"]');
    var changelines = document.querySelectorAll('[data-order="'+ newpos +'"]');

    // If we go down reverse lines to preserve the rows order
    if (move > 0) {
        lines = [].slice.call(lines).reverse();
    }

    for (var i = 0 ; i < lines.length ; i++) {
        var parent = changelines[0].parentNode;
        changePos(lines[i], newpos);
        changePos(changelines[i], parseInt(pos));
        var changeItem = move < 0 ? changelines[0] : changelines[changelines.length - 1].nextSibling;
        parent.insertBefore(lines[i], changeItem);
    }

}

/**
 * Move a row up in the table.
 *
 * @param pos int row counter.
 *
 * @returns false
 */
function orderUp(pos)
{
    if (pos == 0) {
        return false;
    }
    changeOrder(pos, -1);
    return false;
}

/**
 * Move a row down in the table.
 *
 * @param pos int row counter.
 *
 * @returns false
 */
function orderDown(pos)
{
    var lastpos = document.querySelector('[data-order]:last-child').getAttribute('data-order');
    if (pos == lastpos) {
        return false;
    }

    changeOrder(pos, +1);
    return false;
}
