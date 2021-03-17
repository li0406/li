<template>
  <span>{{option[select]}}</span>
</template>

<script>
export default {
  name: 'QzTableCell',
  props: {
    option: Object,
    select: [String, Number],
  },
};
</script>

