<template>
    <div>はろはろー</div>
</template>

<script>
module.exports = {
  props: {
    id: Number,
    label: String,
    value: Number
  },
  computed: {
    active() {
      return this.value === this.id ? 'active' : false
    }
  }
}
 
</script>