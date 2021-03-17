export default {
  methods: {
    QZFetchList(api_name, cb) {
      console.log(api_name)
      api_name().then(res => {
        cb && cb.call()
      })
    }
  }
}
