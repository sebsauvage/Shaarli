/**
 * Change the position counter of a row.
 *
 * @param elem  Element Node to change.
 * @param toPos int     New position.
 */
function changePos(elem, toPos) {
  const elemName = elem.getAttribute('data-line');
  elem.setAttribute('data-order', toPos);
  const hiddenInput = document.querySelector(`[name="order_${elemName}"]`);
  hiddenInput.setAttribute('value', toPos);
}

/**
 * Move a row up or down.
 *
 * @param pos  Element Node to move.
 * @param move int     Move: +1 (down) or -1 (up)
 */
function changeOrder(pos, move) {
  const newpos = parseInt(pos, 10) + move;
  let lines = document.querySelectorAll(`[data-order="${pos}"]`);
  const changelines = document.querySelectorAll(`[data-order="${newpos}"]`);

  // If we go down reverse lines to preserve the rows order
  if (move > 0) {
    lines = [].slice.call(lines).reverse();
  }

  for (let i = 0; i < lines.length; i += 1) {
    const parent = changelines[0].parentNode;
    changePos(lines[i], newpos);
    changePos(changelines[i], parseInt(pos, 10));
    const changeItem = move < 0 ? changelines[0] : changelines[changelines.length - 1].nextSibling;
    parent.insertBefore(lines[i], changeItem);
  }
}

/**
 * Move a row up in the table.
 *
 * @param pos int row counter.
 *
 * @return false
 */
function orderUp(pos) {
  if (pos !== 0) {
    changeOrder(pos, -1);
  }
}

/**
 * Move a row down in the table.
 *
 * @param pos int row counter.
 *
 * @returns false
 */
function orderDown(pos) {
  const lastpos = parseInt(document.querySelector('[data-order]:last-child').getAttribute('data-order'), 10);
  if (pos !== lastpos) {
    changeOrder(pos, 1);
  }
}

(() => {
  /**
   * Plugin admin order
   */
  const orderPA = document.querySelectorAll('.order');
  [...orderPA].forEach((link) => {
    link.addEventListener('click', (event) => {
      event.preventDefault();
      if (event.target.classList.contains('order-up')) {
        orderUp(parseInt(event.target.parentNode.parentNode.getAttribute('data-order'), 10));
      } else if (event.target.classList.contains('order-down')) {
        orderDown(parseInt(event.target.parentNode.parentNode.getAttribute('data-order'), 10));
      }
    });
  });
})();
