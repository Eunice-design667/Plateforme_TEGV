/**
 * Photo fan of the cover page: every few seconds each photo slides one place
 * along the arc; the one leaving on the left fades out and comes back on the right.
 */
export default (count, interval = 3000) => ({
    shift: 0,
    visible: 3,

    init() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        setInterval(() => {
            if (!document.hidden) {
                this.shift++;
            }
        }, interval);
    },

    slot(index) {
        let k = (((index - this.shift) % count) + count) % count;

        if (k > count / 2) {
            k -= count;
        }

        const hidden = Math.abs(k) > this.visible;

        return `--k: ${k}; --ak: ${Math.min(Math.abs(k), this.visible + 1)}; opacity: ${hidden ? 0 : 1}; pointer-events: none`;
    },
});
