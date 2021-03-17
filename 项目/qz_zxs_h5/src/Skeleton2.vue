<template>
  <div class="skeleton-wrapper">
    <section class="skeleton-block" style=" background: #F2F3F7">
      <div class="head" style=" width: 100%;height: 100px;background:  #F2F3F7"></div>
      <img style="display:block; width: 80%; margin: 50px auto;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAmIAAAJCBAMAAACR1LbjAAAAKlBMVEUAAAD///////////////////////////////////////////////////+Gu8ovAAAADXRSTlMABxsccHHLz9HS8vb3eN8nJAAAA6FJREFUeNrt3SHuE0EUwOEpAQwhQaxoT4DdI6D+rgl7gCYYVMsRED0AokFjq7lAbR0Jot1QytwF0UrEPMROJ/l+R/hS1XlvX0r/aPZ0yNoPqbi3uHLO+dKXgs2+0Mo557wtFXvO6tapVOwlq1tjqdgrVreupWKvWd0jRowYMWLEiBEjRowYMWLEiBEjRowYMWLEiBEjRowYMWLEiIkYMWLEiBEjRowYMWLEiBEj9rhidkbuFe+M2Eu6V7yX9ILVrXPxfuU3WDnnnHd2eGOV7/Cm2dMPXvkY2BOXJEmSJEmSJEmSpMeuW7f6dvv745saYM8+N/ze/bWG2LzlCYFfNX5ky6aHKt5VEPvUtNiHCmKHpsW+VxBre9TpJzFixIgRI0aMGDFixIgRI0aMGDH/j/kPdtJWTYu9ryC2aBnsUuMtyXtl/E1806rXnzpv4pIkSZIkSZIkSZIkSZIkSZIkSZIkSZIkSZIkSZIkSXr02j1DWaH90PhnXSt8erdv+wxlhbaNn6GcvlPjn0CfvrHxz+xP3zUxCEaMGDFixIiJGDFixIiJGDFixIgREzFixIgREzFixIgREzFixIgRIyZixIgRIyZixIgRIyZixIgRI0ZMxIgRI0ZMxIgRI0aMmIgRI0aMmIgRI0aMmIgRI0aMGDERI0aMGDERI1ZB7MAg1NX1n2BjWkEIdU4LCKF2LuXFuvQpdRsOxR0H5zslSZIkSZIkSZIkSZKkh61bG3Iqbj8kE3fhibs5hVDbtIQQ6mQ6PTydbgMiugHBILplg4AYMWLEiIkYMWLEiIkYMWLEiBETMWLEiBETMWLEiBETMWLEiBEjJmLEiBEjJmLEiBEjRgwBMWLEiBETMWLEiBETMWLEiBEjJmLEiBEjJmLEiBEjJmLEiBEjRkzEiBEjRkzEiE0vdmAQ6ur6T7AxrSCEOqcFhFA7l/JiXfqUug2H4o6D852SJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJEmSJP1H3doZpOL2Q3KTK3yTa04h1DYtIYQ6uV8Zvl/pRmr0RiqD6B1eBMSIESNGTMSIESNGTMSIESNGjJiIESNGjJiIESNGjJiIESNGjBgxESNGjBgxESNGjBgxYgiIESNGjJiIESNGjJiIESNGjBgxESNGjBgxESNGjBgxESNGjBgxYiJGjBgxYiJGbHqxA4NQV9d/go1pBSHUOS0ghNq5lBfr0qfUbTgUdxzSX4L4v32/4oxVAAAAAElFTkSuQmCC" alt="">
      <div class="head" style=" width: 100%;height: 120px;background:  #F2F3F7"></div>
    </section>
  </div>
</template>

<script>
  export default {
    name: 'skeleton'
  };
</script>

<style scoped>
  .skeleton-block {
    display: flex;
    flex-direction: column;
    padding-top: 8px;
  }
</style>