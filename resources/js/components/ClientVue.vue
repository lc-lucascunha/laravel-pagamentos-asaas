<template>
    <div v-if="canClient" class="card">
        <form @submit.prevent="submitClient">
            <div class="row pb-1 row-header">
                <div class="col-sm-6">
                    <h1>Cadastro</h1>
                </div>
                <div class="col-sm-6 text-end">
                    <button v-if="!loading && edit" type="submit" class="btn btn-180 btn-success">
                        {{client.asaas_id ? 'Salvar Alterações' : 'Finalizar Cadastro'}}
                    </button>
                    <div v-if="!loading && !edit" @click="edit = true" class="btn btn-180 btn-primary">
                        Atualizar Cadastro
                    </div>
                    <div v-if="loading" class="btn btn-180 btn-secondary">
                        Processando...
                    </div>
                </div>
            </div>

            <!-- Informações de Contato -->
            <form-row id="cpf_cnpj" label="CPF / CNPJ">
                <div class="pt-2">{{client.cpf_cnpj}}</div>
            </form-row>
            <form-row id="name" label="Nome" required="true">
                <input v-if="edit" v-model="client.name" id="name" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.name}}</div>
            </form-row>
            <form-row id="email" label="E-mail" required="true">
                <input v-if="edit" v-model="client.email" id="email" placeholder="Ex: exemplo@email.com" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.email}}</div>
            </form-row>
            <form-row id="phone" label="Celular" required="true">
                <input v-if="edit" v-model="client.phone" id="phone" placeholder="Ex: 11911119999" type="number" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.phone}}</div>
            </form-row>

            <!-- Endereço -->
            <form-row id="postal_code" label="CEP" required="true">
                <input v-if="edit" v-model="client.postal_code" id="postal_code" placeholder="Ex: 11111999" type="number" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.postal_code}}</div>
            </form-row>
            <form-row id="address" label="Endereço" required="true">
                <input v-if="edit" v-model="client.address" id="address" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.address}}</div>
            </form-row>
            <form-row id="province" label="Bairro" required="true">
                <input v-if="edit" v-model="client.province" id="province" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.province}}</div>
            </form-row>
            <form-row id="address_number" label="Número" required="true">
                <input v-if="edit" v-model="client.address_number" id="address_number" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.address_number}}</div>
            </form-row>
            <form-row id="complement" label="Complemento" class="mb-2">
                <input v-if="edit" v-model="client.complement" id="complement" type="text" class="form-control">
                <div v-if="!edit" class="pt-2">{{client.complement}}</div>
            </form-row>
        </form>
    </div>
</template>

<script>
import bus from '../eventBus';
import _ from 'lodash';

import FormRow from './forms/RowHorizontalVue.vue';

export default {
    name: 'ClientVue',
    components: {
      FormRow
    },
    data() {
        return {
            loading: false,
            edit: true,

            canClient: false,
            client: {
                id: '',
                asaas_id: '',

                cpf_cnpj: '',
                name: '',
                email: '',
                phone: '',

                postal_code: '',
                address: '',
                address_number: '',
                complement: '',
                province: ''
            }
        }
    },
    created() {
        bus.$on('set-client', (client) => {
            this.setClient(client);
        });
    },
    methods: {
        emitSetClient(client){
            bus.$emit('set-client', client);
        },
        setClient(client){
            this.canClient = (client ? true : false);
            this.client    = client;
            this.edit      = (this.canClient && this.client.asaas_id ? false : true);
        },
        submitClient() {
            this.loading = true;
            axios.put('/api/clients/' + this.client.id, this.client)
                .then(response => {
                    let message = (this.client.asaas_id
                        ? 'Cadastro atualizado com sucesso!'
                        : 'Cadastro finalizado com sucesso e pagamentos liberados!'
                    );
                    this.emitSetClient(response.data);
                    alert(message);
                })
                .catch(error => {
                    let response = error.response;
                    switch (response.status){
                        case 422:
                            alert('VERIFIQUE OS ERROS NO FORMULÁRIO:\n\n'+response.data);
                            break;

                        default:
                            alert(response.data);
                    }
                })
                .finally(() => {
                    this.loading = false;
                });

        }
    }
};
</script>

<style scoped>

</style>
