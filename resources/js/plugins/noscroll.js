import { disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';

export default function (Alpine) {
  Alpine.directive('noscroll', Alpine.skipDuringClone((el, { expression }, { effect, evaluateLater }) => {
    const evaluate = evaluateLater(expression);
    let old = false;
    let undo = () => {};

    effect(() => evaluate(value => {
      if (value === old) return;

      // Ativa o lock
      if (value && !old) {
        // pequeno delay para garantir que o elemento já está no DOM/visível
        setTimeout(() => {
          undo = (() => {
            disableBodyScroll(el, { reserveScrollBarGap: true });
            return () => enableBodyScroll(el);
          })();
        });
      }

      // Desativa o lock
      if (!value && old) {
        undo();
        undo = () => {};
      }

      old = !!value;
    }));
  }));
}
