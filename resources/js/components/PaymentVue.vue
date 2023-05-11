<template>
    <div v-if="canClient && client.asaas_id" class="card">
        <div class="row">
            <div class="col-sm-6">
                <h1>{{ labels[lang].products }}</h1>
            </div>
            <div class="col-sm-6 text-end">
                <button type="button" class="btn btn-success" @click="createProduct">{{ labels[lang].addProduct }}</button>
            </div>
        </div>

        <div class="row pt-0">
            <div class="col-sm-12">
                <div class="input-group">
                    <input type="text" class="form-control" :placeholder="labels[lang].textSearch" v-model="searchText">
                    <div class="input-group-text cursor-pointer" :title="labels[lang].clearSearch" v-if="searchText" @click="searchText = ''">x</div>
                </div>
            </div>
        </div>

        <table class="table">
            <thead>
            <tr>
                <th>{{ labels[lang].id }}</th>
                <th>{{ labels[lang].name }}</th>
                <th>{{ labels[lang].category }}</th>
                <th class="text-center">{{ labels[lang].created }}</th>
                <th class="text-center">{{ labels[lang].updated }}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="product in products" :key="product.id">
                <td>{{ product.id }}</td>
                <td>{{ product.name }}</td>
                <td>{{ product.category.name }}</td>
                <td class="text-center">{{ product.created_at }}</td>
                <td class="text-center">{{ product.updated_at }}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-primary btn-sm me-2" @click="editProduct(product)">{{ labels[lang].edit }}</button>
                    <button type="button" class="btn btn-danger btn-sm" @click="deleteProduct(product)">{{ labels[lang].delete }}</button>
                </td>
            </tr>
            </tbody>
        </table>

        <div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="submitProduct">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel">{{ formTitle }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">{{ labels[lang].name }}</label>
                                <input type="text" class="form-control" id="name" v-model="product.name" required>
                                <div class="invalid-feedback">Please enter a product name.</div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="category">{{ labels[lang].category }}</label>
                                <select class="form-control" id="category" v-model="product.category_id" required>
                                    <option></option>
                                    <option v-for="category in categories" :value="category.id">{{ category.name }}</option>
                                </select>
                                <div class="invalid-feedback">Please select a category.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ labels[lang].cancel }}</button>
                            <button type="submit" class="btn btn-primary">{{ formAction }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import bus from '../eventBus';
import _ from 'lodash';

export default {
    name: 'PaymentVue',
    data() {
        return {
            canClient: false,
            client: {
                asaas_id: '',
            },

            categories: [],
            products: [],
            product: {
                id: null,
                name: '',
                category_id: null,
                created_at: '',
                updated_at: '',
            },
            formAction: '',
            formTitle: '',
            searchText: '',
            lang: 'en',
            labels: {
                en: {
                    products: 'Products',
                    addProduct: 'Add Product',
                    editProduct: 'Edit Product',

                    id: 'ID',
                    name: 'Name',
                    category: 'Category',
                    created: 'Created',
                    updated: 'Updated',

                    edit: 'Edit',
                    delete: 'Delete',
                    create: 'Create',
                    update: 'Update',
                    cancel: 'Cancel',

                    clearSearch: 'Clear search',

                    textSearch: 'Search by Name or Category...',
                    textConfirDelete: 'Are you sure you want to delete this product?',
                },
                pt: {
                    products: 'Produtos',
                    addProduct: 'Adicionar Produto',
                    editProduct: 'Editar Produto',

                    id: 'ID',
                    name: 'Nome',
                    category: 'Categoria',
                    created: 'Criado em',
                    updated: 'Atualizado em',

                    edit: 'Editar',
                    delete: 'Excluir',
                    create: 'Cadastrar',
                    update: 'Atualizar',
                    cancel: 'Cancelar',

                    clearSearch: 'Limpar busca',

                    textSearch: 'Buscar por Nome ou Categoria...',
                    textConfirDelete: 'Tem certeza de que deseja excluir este produto?',
                },
            },
        }
    },
    created() {
        bus.$on('set-client', (client) => {
            this.setClient(client);
        });

        this.fetchProducts();
        this.fetchCategories();

        bus.$on('category-created', () => {
            this.fetchCategories();
        });

        bus.$on('category-updated', () => {
            this.fetchCategories();
            this.fetchProducts();
        });

        bus.$on('category-deleted', () => {
            this.fetchCategories();
            this.fetchProducts();
        });

        bus.$on('category-search', (category) => {
            this.searchText = category;
        });

        bus.$on('languagem-select', (lang) => {
            this.fetchLang(lang);
        });
    },
    watch: {
        searchText: _.debounce(function() {
            this.fetchProducts();
        }, 500)
    },
    methods: {
        setClient(client){
            this.canClient = (client ? true : false);
            this.client = client;
        },
        emitProducts(event){
            bus.$emit('product-'+event);
        },
        fetchLang(lang) {
            this.lang = lang;
        },
        fetchProducts() {
            let url = '/api/products';

            if (this.searchText) {
                url += '?q=' + encodeURIComponent(this.searchText.trim());
            }

            axios.get(url)
                .then(response => {
                    this.products = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        fetchCategories() {
            axios.get('/api/categories')
                .then(response => {
                    this.categories = response.data;
                })
                .catch(error => {
                    console.log(error);
                });
        },
        createProduct() {
            this.formAction = this.labels[this.lang].create;
            this.formTitle = this.labels[this.lang].addProduct;
            this.product.id = null;
            this.product.name = '';
            this.product.category_id = null;
            this.product.created_at = '';
            this.product.updated_at = '';
            $('#productModal').modal('show');
        },
        editProduct(product) {
            this.formAction = this.labels[this.lang].update;
            this.formTitle = this.labels[this.lang].editProduct;
            this.product.id = product.id;
            this.product.name = product.name;
            this.product.category_id = product.category_id;
            this.product.created_at = product.created_at;
            this.product.updated_at = product.updated_at;
            $('#productModal').modal('show');
        },
        submitProduct() {
            if (this.formAction === this.labels[this.lang].create) {
                axios.post('/api/products', this.product)
                    .then(response => {
                        $('#productModal').modal('hide');
                        this.searchText = '';
                        this.fetchProducts();
                        this.emitProducts('created');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            } else if (this.formAction === this.labels[this.lang].update) {
                axios.put('/api/products/' + this.product.id, this.product)
                    .then(response => {
                        $('#productModal').modal('hide');
                        this.fetchProducts();
                        this.emitProducts('updated');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
        deleteProduct(product) {
            if (confirm(this.labels[this.lang].textConfirDelete)) {
                axios.delete('/api/products/' + product.id)
                    .then(response => {
                        this.fetchProducts();
                        this.emitProducts('deleted');
                    })
                    .catch(error => {
                        console.log(error);
                    });
            }
        },
    }
};
</script>

<style scoped>

</style>
