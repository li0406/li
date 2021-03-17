<template>
  <span>{{ dateFormat }}</span>
</template>

<script>
import dayjs from 'dayjs';

export default {
  name: 'QzTimestampFormat',
  props: {
    timestamp: String,
  },
  computed: {
    dateFormat() {
      return dayjs.unix(this.timestamp).format('YYYY-MM-DD HH:mm:ss');
    },
  },
};
</script>
