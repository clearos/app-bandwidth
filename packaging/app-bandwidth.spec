
Name: app-bandwidth
Epoch: 1
Version: 1.4.37
Release: 1%{dist}
Summary: Bandwidth Manager
License: GPLv3
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-network

%description
Bandwidth Manager is an essential tool to manage the performance of the entire network.  You can ensure that services such as web browsing, VoIP, SSH and others are guaranteed enough bandwidth.

%package core
Summary: Bandwidth Manager - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-network-core
Requires: app-firewall-core >= 1:1.4.15

%description core
Bandwidth Manager is an essential tool to manage the performance of the entire network.  You can ensure that services such as web browsing, VoIP, SSH and others are guaranteed enough bandwidth.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/bandwidth
cp -r * %{buildroot}/usr/clearos/apps/bandwidth/

install -d -m 0755 %{buildroot}/var/clearos/bandwidth
install -d -m 0755 %{buildroot}/var/clearos/bandwidth/backup/
install -D -m 0644 packaging/bandwidth.conf %{buildroot}/etc/clearos/bandwidth.conf

%post
logger -p local6.notice -t installer 'app-bandwidth - installing'

%post core
logger -p local6.notice -t installer 'app-bandwidth-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/bandwidth/deploy/install ] && /usr/clearos/apps/bandwidth/deploy/install
fi

[ -x /usr/clearos/apps/bandwidth/deploy/upgrade ] && /usr/clearos/apps/bandwidth/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-bandwidth - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-bandwidth-core - uninstalling'
    [ -x /usr/clearos/apps/bandwidth/deploy/uninstall ] && /usr/clearos/apps/bandwidth/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/bandwidth/controllers
/usr/clearos/apps/bandwidth/htdocs
/usr/clearos/apps/bandwidth/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/bandwidth/packaging
%exclude /usr/clearos/apps/bandwidth/tests
%dir /usr/clearos/apps/bandwidth
%dir /var/clearos/bandwidth
%dir /var/clearos/bandwidth/backup/
/usr/clearos/apps/bandwidth/deploy
/usr/clearos/apps/bandwidth/language
/usr/clearos/apps/bandwidth/libraries
%config(noreplace) /etc/clearos/bandwidth.conf
