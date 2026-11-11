const DURATION = 5000;

/**
 * Story viewer for the "Affiches du moment" row: auto-advances every 5 seconds,
 * tap right/left to move, hold to pause.
 */
export default (stories) => ({
    stories,
    active: false,
    storyIndex: 0,
    posterIndex: 0,
    progress: 0,
    paused: false,
    seen: [],
    timer: null,
    startedAt: 0,
    elapsed: 0,

    get story() {
        return this.stories[this.storyIndex];
    },

    get current() {
        return this.story?.posters[this.posterIndex];
    },

    open(index) {
        this.storyIndex = index;
        this.posterIndex = 0;
        this.active = true;
        document.body.style.overflow = 'hidden';
        this.play();
    },

    close() {
        if (!this.active) {
            return;
        }

        this.markSeen();
        this.active = false;
        document.body.style.overflow = '';
        cancelAnimationFrame(this.timer);
    },

    play() {
        cancelAnimationFrame(this.timer);
        this.elapsed = 0;
        this.progress = 0;
        this.startedAt = performance.now();
        this.tick();
    },

    tick() {
        this.timer = requestAnimationFrame((now) => {
            if (!this.active) {
                return;
            }

            if (!this.paused) {
                this.progress = Math.min(100, ((this.elapsed + now - this.startedAt) / DURATION) * 100);

                if (this.progress >= 100) {
                    return this.next();
                }
            }

            this.tick();
        });
    },

    pause() {
        if (this.paused) {
            return;
        }

        this.paused = true;
        this.elapsed += performance.now() - this.startedAt;
    },

    resume() {
        if (!this.paused) {
            return;
        }

        this.paused = false;
        this.startedAt = performance.now();
    },

    next() {
        if (this.posterIndex < this.story.posters.length - 1) {
            this.posterIndex++;
            return this.play();
        }

        this.markSeen();

        if (this.storyIndex < this.stories.length - 1) {
            this.storyIndex++;
            this.posterIndex = 0;
            return this.play();
        }

        this.close();
    },

    previous() {
        if (this.posterIndex > 0) {
            this.posterIndex--;
        } else if (this.storyIndex > 0) {
            this.storyIndex--;
            this.posterIndex = 0;
        }

        this.play();
    },

    markSeen() {
        if (this.story && !this.seen.includes(this.story.id)) {
            this.seen.push(this.story.id);
        }
    },
});
