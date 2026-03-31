<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Validation\Rule;

class AdminUserCrud extends Component
{
    public $users;
    public $name, $email, $role, $user_id;
    public $isEdit = false;
    public $showModal = false;
    public $roles = [
        'admin', 'super-admin', 'direction', 'caisse', 'agence', 'guichet', 'agent', 'evaluateur', 'pca', 'pca-entite', 'pca-caisse', 'pca-agence', 'pca-guichet', 'pca-direction', 'pca-region', 'pca-rcpb', 'pca-structure', 'pca-evaluateur', 'pca-admin', 'pca-super-admin', 'pca-user', 'pca-role', 'pca-evaluation', 'pca-objectifs', 'pca-indicateurs', 'pca-kpi', 'pca-dashboard', 'pca-settings', 'pca-statistiques', 'pca-rapport', 'pca-historique', 'pca-notification', 'pca-message', 'pca-tache', 'pca-document', 'pca-fichier', 'pca-photo', 'pca-video', 'pca-audio', 'pca-archive', 'pca-import', 'pca-export', 'pca-backup', 'pca-restore', 'pca-reset', 'pca-logs', 'pca-activity', 'pca-permission', 'pca-access', 'pca-auth', 'pca-profile', 'pca-password', 'pca-email', 'pca-phone', 'pca-address', 'pca-contact', 'pca-info', 'pca-help', 'pca-support', 'pca-feedback', 'pca-bug', 'pca-feature', 'pca-request', 'pca-issue', 'pca-task', 'pca-project', 'pca-team', 'pca-member', 'pca-group', 'pca-organization', 'pca-entreprise', 'pca-societe', 'pca-association', 'pca-cooperative', 'pca-union', 'pca-federation', 'pca-confederation', 'pca-network', 'pca-system', 'pca-plateforme', 'pca-application', 'pca-service', 'pca-module', 'pca-component', 'pca-widget', 'pca-plugin', 'pca-extension', 'pca-theme', 'pca-template', 'pca-layout', 'pca-menu', 'pca-sidebar', 'pca-navbar', 'pca-footer', 'pca-header', 'pca-banner', 'pca-logo', 'pca-avatar', 'pca-icon', 'pca-image', 'pca-thumbnail', 'pca-cover', 'pca-background', 'pca-color', 'pca-font', 'pca-style', 'pca-css', 'pca-js', 'pca-html', 'pca-xml', 'pca-json', 'pca-yaml', 'pca-md', 'pca-txt', 'pca-csv', 'pca-xls', 'pca-xlsx', 'pca-doc', 'pca-docx', 'pca-pdf', 'pca-ppt', 'pca-pptx', 'pca-odt', 'pca-ods', 'pca-odp', 'pca-odg', 'pca-odi', 'pca-odf', 'pca-odc', 'pca-odb', 'pca-odm', 'pca-ott', 'pca-ots', 'pca-otp', 'pca-otg', 'pca-oti', 'pca-otf', 'pca-otc', 'pca-otb', 'pca-otm', 'pca-oth', 'pca-otd', 'pca-otn', 'pca-otq', 'pca-otr', 'pca-ots', 'pca-ott', 'pca-otu', 'pca-otv', 'pca-otw', 'pca-otx', 'pca-oty', 'pca-otz', 'pca-oa', 'pca-ob', 'pca-oc', 'pca-od', 'pca-oe', 'pca-of', 'pca-og', 'pca-oh', 'pca-oi', 'pca-oj', 'pca-ok', 'pca-ol', 'pca-om', 'pca-on', 'pca-oo', 'pca-op', 'pca-oq', 'pca-or', 'pca-os', 'pca-ot', 'pca-ou', 'pca-ov', 'pca-ow', 'pca-ox', 'pca-oy', 'pca-oz', 'pca-pa', 'pca-pb', 'pca-pc', 'pca-pd', 'pca-pe', 'pca-pf', 'pca-pg', 'pca-ph', 'pca-pi', 'pca-pj', 'pca-pk', 'pca-pl', 'pca-pm', 'pca-pn', 'pca-po', 'pca-pp', 'pca-pq', 'pca-pr', 'pca-ps', 'pca-pt', 'pca-pu', 'pca-pv', 'pca-pw', 'pca-px', 'pca-py', 'pca-pz', 'pca-qa', 'pca-qb', 'pca-qc', 'pca-qd', 'pca-qe', 'pca-qf', 'pca-qg', 'pca-qh', 'pca-qi', 'pca-qj', 'pca-qk', 'pca-ql', 'pca-qm', 'pca-qn', 'pca-qq', 'pca-qr', 'pca-qs', 'pca-qt', 'pca-qu', 'pca-qv', 'pca-qw', 'pca-qx', 'pca-qy', 'pca-qz', 'pca-ra', 'pca-rb', 'pca-rc', 'pca-rd', 'pca-re', 'pca-rf', 'pca-rg', 'pca-rh', 'pca-ri', 'pca-rj', 'pca-rk', 'pca-rl', 'pca-rm', 'pca-rn', 'pca-ro', 'pca-rp', 'pca-rq', 'pca-rr', 'pca-rs', 'pca-rt', 'pca-ru', 'pca-rv', 'pca-rw', 'pca-rx', 'pca-ry', 'pca-rz', 'pca-sa', 'pca-sb', 'pca-sc', 'pca-sd', 'pca-se', 'pca-sf', 'pca-sg', 'pca-sh', 'pca-si', 'pca-sj', 'pca-sk', 'pca-sl', 'pca-sm', 'pca-sn', 'pca-so', 'pca-sp', 'pca-sq', 'pca-sr', 'pca-ss', 'pca-st', 'pca-su', 'pca-sv', 'pca-sw', 'pca-sx', 'pca-sy', 'pca-sz', 'pca-ta', 'pca-tb', 'pca-tc', 'pca-td', 'pca-te', 'pca-tf', 'pca-tg', 'pca-th', 'pca-ti', 'pca-tj', 'pca-tk', 'pca-tl', 'pca-tm', 'pca-tn', 'pca-to', 'pca-tp', 'pca-tq', 'pca-tr', 'pca-ts', 'pca-tt', 'pca-tu', 'pca-tv', 'pca-tw', 'pca-tx', 'pca-ty', 'pca-tz', 'pca-ua', 'pca-ub', 'pca-uc', 'pca-ud', 'pca-ue', 'pca-uf', 'pca-ug', 'pca-uh', 'pca-ui', 'pca-uj', 'pca-uk', 'pca-ul', 'pca-um', 'pca-un', 'pca-uo', 'pca-up', 'pca-uq', 'pca-ur', 'pca-us', 'pca-ut', 'pca-uu', 'pca-uv', 'pca-uw', 'pca-ux', 'pca-uy', 'pca-uz', 'pca-va', 'pca-vb', 'pca-vc', 'pca-vd', 'pca-ve', 'pca-vf', 'pca-vg', 'pca-vh', 'pca-vi', 'pca-vj', 'pca-vk', 'pca-vl', 'pca-vm', 'pca-vn', 'pca-vo', 'pca-vp', 'pca-vq', 'pca-vr', 'pca-vs', 'pca-vt', 'pca-vu', 'pca-vv', 'pca-vw', 'pca-vx', 'pca-vy', 'pca-vz', 'pca-wa', 'pca-wb', 'pca-wc', 'pca-wd', 'pca-we', 'pca-wf', 'pca-wg', 'pca-wh', 'pca-wi', 'pca-wj', 'pca-wk', 'pca-wl', 'pca-wm', 'pca-wn', 'pca-wo', 'pca-wp', 'pca-wq', 'pca-wr', 'pca-ws', 'pca-wt', 'pca-wu', 'pca-wv', 'pca-ww', 'pca-wx', 'pca-wy', 'pca-wz', 'pca-xa', 'pca-xb', 'pca-xc', 'pca-xd', 'pca-xe', 'pca-xf', 'pca-xg', 'pca-xh', 'pca-xi', 'pca-xj', 'pca-xk', 'pca-xl', 'pca-xm', 'pca-xn', 'pca-xo', 'pca-xp', 'pca-xq', 'pca-xr', 'pca-xs', 'pca-xt', 'pca-xu', 'pca-xv', 'pca-xw', 'pca-xx', 'pca-xy', 'pca-xz', 'pca-ya', 'pca-yb', 'pca-yc', 'pca-yd', 'pca-ye', 'pca-yf', 'pca-yg', 'pca-yh', 'pca-yi', 'pca-yj', 'pca-yk', 'pca-yl', 'pca-ym', 'pca-yn', 'pca-yo', 'pca-yp', 'pca-yq', 'pca-yr', 'pca-ys', 'pca-yt', 'pca-yu', 'pca-yv', 'pca-yw', 'pca-yx', 'pca-yy', 'pca-yz', 'pca-za', 'pca-zb', 'pca-zc', 'pca-zd', 'pca-ze', 'pca-zf', 'pca-zg', 'pca-zh', 'pca-zi', 'pca-zj', 'pca-zk', 'pca-zl', 'pca-zm', 'pca-zn', 'pca-zo', 'pca-zp', 'pca-zq', 'pca-zr', 'pca-zs', 'pca-zt', 'pca-zu', 'pca-zv', 'pca-zw', 'pca-zx', 'pca-zy', 'pca-zz'
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'role' => 'required|string',
    ];

    public function mount()
    {
        $this->users = User::all();
    }

    public function render()
    {
        return view('livewire.admin.admin-user-crud');
    }

    public function create()
    {
        $this->reset(['name', 'email', 'role', 'user_id', 'isEdit']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->user_id = $user->id;
        $this->isEdit = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user_id),
            ],
            'role' => ['required', Rule::in($this->roles)],
        ]);

        if ($this->isEdit) {
            $user = User::findOrFail($this->user_id);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
            ]);
        } else {
            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role,
                'password' => bcrypt('password'), // Default password
            ]);
        }
        $this->users = User::all();
        $this->showModal = false;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $this->users = User::all();
    }
}
