<template>
    <nav class="card">
        <div class="row p-1">
            <div class="col-sm-8 title">
                <h4><a href="/" title="Reiniciar aplicação" class="navbar-brand">{{title}}</a></h4>
            </div>
            <div class="col-sm-4">
                <div class="input-group right">
                    <input v-model="cpf_cnpj" :disabled="loading || cpf_cnpj && client ? true : false" id="cpf_cnpj" type="text" class="form-control" placeholder="Informe o seu CPF ou CNPJ" >
                    <!-- Ações -->
                    <button @click="fetchClient" v-if="!loading && !client" type="button" class="btn btn-180 btn-success btn-sm me-2">Entrar / Cadastrar</button>
                    <button v-if="loading" type="button" class="btn btn-180 btn-secondary btn-sm me-2">Processando...</button>
                    <button @click="cpf_cnpj = ''" v-if="!loading && cpf_cnpj && client" type="button" class="btn btn-180 btn-danger btn-sm me-2">Encerrar Acesso</button>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import bus from '../eventBus';
import _ from 'lodash';

export default {
    name: 'HeaderVue',
    props: ['title'],
    data() {
        return {
            loading: false,

            cpf_cnpj: '',
            client: null
        }
    },
    watch: {
        cpf_cnpj: _.debounce(function() {
            this.cpf_cnpj = this.cpf_cnpj.trim();
            if(!this.cpf_cnpj) {
                this.fetchClient();
            }
        })
    },
    methods: {
        emitSetClient(){
            bus.$emit('set-client', this.client);
        },
        fetchClient() {
            if(!this.cpf_cnpj){
                this.client = null;
                this.emitSetClient();
            }
            else {
                this.loading = true;
                axios.post('/api/clients', {cpf_cnpj: this.cpf_cnpj})
                    .then(response => {
                        this.client = response.data;
                        this.cpf_cnpj = this.client.cpf_cnpj;
                        this.emitSetClient();
                    })
                    .catch(error => {
                        alert(error.response.data);
                    })
                    .finally(() => {
                        this.loading = false;
                    });

            }
        }
    }
};
</script>

<style scoped>
    .title{
        padding-top: 0.4rem;
    }
</style>
