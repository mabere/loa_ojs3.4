{**
* templates/verificationPage.tpl
* Tampilan untuk halaman verifikasi LoA.
*}
{include file="frontend/components/header.tpl"}

<div class="page page_message">
    <div class="container-fluid">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <br>
                {if $isValid}
                <div class="alert alert-success" role="alert">
                    <h4 class="alert-heading">{translate key="plugins.generic.loa.verification.valid"}</h4>
                    <p>{translate key="plugins.generic.loa.verification.validMessage"}</p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{$articleTitle|escape}</h5>
                        <p class="card-text">
                            <strong>{translate key="submission.authors"}:</strong> {$authorNamesString|escape}<br>
                            <strong>{translate key="plugins.generic.loa.submissionId"}:</strong>
                            {$submissionId|escape}<br>
                            <strong>{translate key="manager.setup.contextName"}:</strong> {$journalTitle|escape}
                        </p>
                    </div>
                </div>
                {else}
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">{translate key="plugins.generic.loa.verification.invalid"}</h4>
                    <p>{translate key="plugins.generic.loa.verification.invalidMessage"}</p>
                </div>
                {/if}
                <br>
            </div>
        </div>
    </div>
</div>

{include file="frontend/components/footer.tpl"}