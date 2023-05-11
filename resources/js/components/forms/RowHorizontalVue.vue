<template>
    <div class="row p-1">
        <label :for="id" class="col-sm-4 col-form-label">
            <span v-if="required" class="text-danger">*</span> {{label}}
        </label>
        <div class="col-sm-8">
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
        'required'
    ],
    data() {
        return {

        }
    }
}
</script>

<style scoped>

</style>
