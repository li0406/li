<template>
  <span>{{ dateFormat }}</span>
</template>

<script>
import dayjs from 'dayjs';

export default {
  name: 'QzDateFormat',
  props: {
    date: String,
  },
  computed: {
    dateFormat() {
      return dayjs(this.date).format('HH:mm:ss');
    },
  },
};
</script>
