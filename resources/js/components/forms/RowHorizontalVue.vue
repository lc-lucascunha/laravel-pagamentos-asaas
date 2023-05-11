<template>
    <div class="row p-1">
        <label :for="id" class="col-sm-2 col-form-label">{{label}}</label>
        <div class="col-sm-10">
            <slot/>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FormRow',
    props: [
        'id',
        'label',
    ],
    data() {
        return {

        }
    }
}
</script>

<style scoped>

</style>
