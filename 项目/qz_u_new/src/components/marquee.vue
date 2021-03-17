<script>
export default {
  name: 'MarqueeText',
  functional: true,
  props: {
    duration: {
      type: Number,
      default: 15,
    },
    repeat: {
      type: Number,
      default: 1,
    },
    paused: {
      type: Boolean,
      default: false,
    },
  },
  render(h, { $style, props: { duration, repeat, paused }, children, data: { staticClass, key } }) {
    const text = h(
      'div',
      {
        class: $style.text,
        style: {
          animationDuration: `${duration}s`,
        },
      },
      children,
    );
    return h(
      'div',
      {
        key,
        class: [staticClass, $style.wrap],
      },
      [
        h(
          'div',
          {
            class: [paused ? $style.paused : undefined, $style.content],
          },
          Array(repeat).fill(text),
        ),
      ],
    );
  },
};
</script>

<style module>
.wrap {
  overflow: hidden;
}
.content {
  height: 100000px;
}
.text {
  float: left;
  animation-name: animation;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
}
.paused .text {
  animation-play-state: paused;
}
@keyframes animation {
  0% {
    transform: translateY(0);
  }
  100% {
    transform: translateY(-100%);
  }
}
</style>
